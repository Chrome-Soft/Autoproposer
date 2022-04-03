<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Recommender;


use App\Contracts\IHttpClient;
use App\Contracts\IRecommendationItem;
use App\Proposer;
use App\ProposerItem;
use App\ProposerItemType;
use App\Recommendation;
use App\Segment;
use App\Services\Recommender\Fallback\FallbackFactory;
use App\UserData;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecommenderService implements IRecommenderService
{
    /**
     * @var IHttpClient
     */
    protected $httpClient;
    /**
     * @var string
     */
    protected $baseUrl;
    /**
     * @var FallbackFactory
     */
    protected $fallbackFactory;

    public function __construct(IHttpClient $httpClient, FallbackFactory $fallbackFactory, string $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $baseUrl;

        $this->httpClient->setBaseUrl($this->baseUrl);
        $this->fallbackFactory = $fallbackFactory;
    }

    public function segmentify(UserData $userData): Segment
    {
        $segmentId = $userData->segmentify();
        return Segment::where('id', $segmentId)->first();

        $data = [
            'device_is_mobile'          => $userData->device_is_mobile,
            'device_manufacturer'       => $userData->device_manufacturer,
            'location_country_name'     => $userData->location_country_name,
            'location_city_name'        => $userData->location_city_name,
            'device_screen_width'       => $userData->device_screen_width,
            'device_screen_height'      => $userData->device_screen_height,
            'segment_id'                => null,
            'device_product'            => $userData->device_product,
            'browser_name'              => $userData->browser_name,
        ];

        $data = $this->httpClient->post('/segmentify', ['user_data' => $data]);
        return $this->mapSegmentifyResult($data);
    }

    public function recommend(Segment $segment, Proposer $proposer)
    {
        $customProducts = $segment->getProductsByType();
        $proposerItems = $proposer->getItemsWithLabels();
        $excludedIds = $this->getExcludedProductIds($customProducts, $proposerItems);

        $recommendations = Recommendation::getBySegment($segment, $excludedIds, $proposer->max_item_number - $proposerItems->count());
        // ez fogja nem unique módon beletenni az összes elérhető terméket
        $recommendations = $this->appendCustomItems($recommendations, $customProducts, $proposerItems, $proposer->max_item_number);

        $recommendations = $this->appendFallbackProducts($recommendations, $segment, $proposer->max_item_number - $recommendations->count());

        return $recommendations->toArray();
    }

    protected function appendFallbackProducts(Collection $recommendations, Segment $segment, $n): Collection
    {
        if ($n <= 0) return $recommendations;

        $fallbackComposition = $this->fallbackFactory->createDefaultComposition();

        $excludedIds = $recommendations->map(function (IRecommendationItem $item) { return $item->getId(); })->all();
        $fallbackProducts = $fallbackComposition->getProducts($segment, $n, $excludedIds);

        foreach ($fallbackProducts as $fallbackProduct) {
            $recommendations->push($fallbackProduct);
        }

        return $recommendations;
    }

    public function waitForSegment($cookieId)
    {
        $tries = 0;
        $userData = null;
        $segmentId = null;

        // TODO ezt majd valahogy másképp kéne megoldani. Tesztelni kell, hogy mennyire lassú a ProcessUserData queue nélkül
        // TODO ha nem olyan vészes, akkor nem kell queue, és kliensen lesz visszatérési érték, és ide konkrét segment id -t lehet küldeni
        while (!$userData && !$segmentId && $tries <= 20) {
            $userData = UserData::where('cookie_id', $cookieId)->first();
            $segmentId = $userData ? $userData->segment_id : null;
            $tries++;
            usleep(200000);
        }

        return ($userData)
            ? Segment::where('id', $userData->segment_id)->first()
            : Segment::where('is_default', 1)->first();
    }

    protected function getExcludedProductIds(array $customProducts, Collection $proposerItems)
    {
        $excludedIds = [];
        foreach ($customProducts as $type => $products) {
            foreach ($products as $product) {
                $excludedIds[] = $product->id;
            }
        }

        return $proposerItems
            ->where('type_key', '=', ProposerItemType::TYPE_PRODUCT)
            ->pluck('product_id')
            ->merge($excludedIds)
            ->unique()
            ->all();
    }

    public function storeRecommendations(array $segmentIds)
    {
        $recommendations = $this->httpClient->post('/recommend-batch', [
            'segment_ids'   => $segmentIds
        ]);

        if (count($recommendations) > 0) {
            Recommendation::truncate();
        }

        foreach ($recommendations as $segmentId => $productIds) {
            $data = [];
            $order = 1;

            foreach ($productIds as $productId) {
                $data[] = [
                    'segment_id'    => $segmentId,
                    'product_id'    => $productId,
                    'order'         => $order,
                    'created_at'    => Carbon::now()
                ];

                $order++;
            }

            Recommendation::insert($data);
        }
    }

    public function trainNeuralNetwork()
    {
        $this->httpClient->post('/train-model', []);
    }

    /**
     * ProposerItemeket és custom Productokat tesz hozzá az ajánlásokhoz
     */
    protected function appendCustomItems(Collection $recommendations, array $customProducts, Collection $proposerItems, $maxItems)
    {
        $recommendations = $this->appendCustomProducts($recommendations, $customProducts, 'always');
        $recommendations = $this->appendCustomProducts($recommendations, $customProducts, 'optional');

        $recommendations = $recommendations->slice(0, $maxItems - $proposerItems->count());

        foreach ($proposerItems->sortByDesc('id') as $item) {
            /** @var ProposerItem $item */
            $ids = $this->getUniqueIds($recommendations);

            if (!$item->isInBuckets($ids)) {
                $recommendations->prepend($item);
            }
        }

        return $recommendations;
    }

    protected function appendCustomProducts($recommendations, $customProducts, $type)
    {
        foreach ($customProducts[$type] as $product) {
            $ids = $this->getUniqueIds($recommendations);

            if (!in_array($product->id, $ids['productIds'])) {
                $fn = $type == 'always' ? 'prepend' : 'push';
                $recommendations->{$fn}($product);
            }
        }

        return $recommendations;
    }

    protected function getUniqueIds(Collection $recommendations): array
    {
        $result = [
            'proposerIds'   => [],
            'productIds'    => []
        ];

        foreach ($recommendations as $recommendation) {
            /** @var IRecommendationItem $recommendation  */
            $result[$recommendation->getBucketName()][] = $recommendation->getId();
        }

        return $result;
    }

    protected function mapSegmentifyResult(array $result): Segment
    {
        $max = 0;
        $maxSequence = 0;

        foreach ($result[0] as $i => $p) {
            if ($p > $max) {
                $max = $p;
                $maxSequence = $i;
            }
        }

        return either(Segment::where('sequence', $maxSequence)->first(), Segment::where('is_default', 1)->first());
    }
}
