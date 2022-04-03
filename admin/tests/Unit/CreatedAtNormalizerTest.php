<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace Tests\Unit;

use App\Product;
use App\Services\ProductAttributeSyncer;
use App\Services\Segment\ExpressionNormalizer\CreatedAtNormalizer;
use App\Services\Segment\ExpressionNormalizer\VersionNormalizer;
use Carbon\Carbon;
use Tests\TestCase;

class CreatedAtNormalizerTest extends TestCase
{
    /** @test */
    public function it_returns_true_if_value_is_only_date()
    {
        $date = '2019-08-01';
        $normalizer = new CreatedAtNormalizer('created_at', '>=', 'and', $date);

        $format = $this->invokeMethod($normalizer, 'getFormat', [new Carbon($date)]);

        $this->assertEquals(CreatedAtNormalizer::DATE, $format);
    }

    /** @test */
    public function it_returns_true_if_value_is_only_time()
    {
        $date = '19:33';
        $normalizer = new CreatedAtNormalizer('created_at', '>=', 'and', $date);

        $format = $this->invokeMethod($normalizer, 'getFormat', [new Carbon($date)]);

        $this->assertEquals(CreatedAtNormalizer::TIME, $format);
    }

    /** @test */
    public function it_returns_true_if_value_is_datetime()
    {
        $date = '2019-09-01 19:33';
        $normalizer = new CreatedAtNormalizer('created_at', '>=', 'and', $date);

        $format = $this->invokeMethod($normalizer, 'getFormat', [new Carbon($date)]);

        $this->assertEquals(CreatedAtNormalizer::DATETIME, $format);
    }

    /** @test */
    public function regression_it_returns_true_if_value_is_datetimewith_seconds()
    {
        $date = '2019-09-01 19:33:21';
        $normalizer = new CreatedAtNormalizer('created_at', '>=', 'and', $date);

        $format = $this->invokeMethod($normalizer, 'getFormat', [new Carbon($date)]);

        $this->assertEquals(CreatedAtNormalizer::DATETIME, $format);
    }
}
