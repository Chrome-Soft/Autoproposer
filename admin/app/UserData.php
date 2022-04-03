<?php

namespace App;

class UserData extends Model
{
    use Listable;

    public function getListData(Segment $segment, array $paging, array $filters)
    {
        $data = $segment->getUserData($paging, $filters);

        return [
            'columns'       => $this->getListColumns(),
            'items'         => $this->castItems($data['items']),
            'count'         => $data['count'],
            'hiddenColumns' => array_flip($this->hiddenColumns()),
            'actions'       => []
        ];
    }

    protected function excludedColumns()
    {
        return ['user_id', 'cookie_id', 'partner_external_id', 'timezone_offset', 'browser_user_agent', 'updated_at', 'connection_real_type', 'connection_effective_type', 'segment_id'];
    }

    protected function columnOrders()
    {
        return [
            'created_at' => 0,
            'device_manufacturer'=> 1,
            'device_product'  => 2,
            'device_is_mobile' => 3,
            'device_memory'     => 4,
            'device_screen_width'=> 5,
            'device_screen_height'=> 6,
            'os_architecture' => 7,
            'os_name'        => 8,
            'os_version'     => 9,
            'browser_name' => 10,
            'browser_version' => 11,
            'connection_bandwidth' => 12,
            'location_country_name' => 13,
            'location_city_name'       => 14,
            'location_postal_code'     => 15,
            'location_subdivision_name' => 16,

            'location_longitude' => 100,
            'location_latitude' => 100,
            'location_real_postal_code' => 90,
            'location_real_city_name' => 90,
            'sex' => 90,
            'birth_date' => 90,
            'email_domain' => 90,
            'phone_provider' => 90
        ];
    }

    protected function customCasters()
    {
        return [
            'connection_bandwidth'  => function ($value) { return $value ? "{$value}Mb/s" : '-'; },
            'os_architecture'       => 'bit',
            'device_memory'         => function ($value) { return $value ? "{$value}GB" : '-'; },
            'device_screen_width'   => 'px',
            'device_screen_height'  => 'px',
            'device_is_mobile'      => function ($value) { return $this->boolCaster($value); }
        ];
    }

    /**
     * Beállítja azt a segment_id -t, amelyikbe a user_data sor tartozik kritériumok alapján.
     * Fallback -ként a default 'egyéb' szegmenst használja.
     * UserData mentéskor használjuk, amikor még nincs
     * szegmensbe sorolva az adott UserData sor.
     * @return int
     */
    public function segmentify(): int
    {
        $segments = Segment::where('is_default', 0)->get();
        $possibleSegments = [];

        foreach ($segments as $segment) {
            $query = $segment->buildQuery($this->id);
            $item = $query->first();

            if (!$item) continue;

            $possibleSegments[] = $segment;
        }

        if (empty($possibleSegments)) {
            $defaultSegment = Segment::where('is_default', 1)->first();
            $this->segment_id = $defaultSegment->id;
            $this->save();

            return $defaultSegment->id;
        }

        $bestSegment = $possibleSegments[0];
        foreach ($possibleSegments as $item) {
            if ($item->getCriterias()->count() > $bestSegment->getCriterias()->count()) {
                $bestSegment = $item;
            }
        }

        $this->segment_id = $bestSegment->id;
        $this->save();

        return $bestSegment->id;
    }
}
