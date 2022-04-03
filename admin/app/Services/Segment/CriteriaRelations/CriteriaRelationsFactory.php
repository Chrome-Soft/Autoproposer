<?php

namespace App\Services\Segment\CriteriaRelations;

class CriteriaRelationsFactory
{
    public static function create($criteriaSlug): CriteriaRelations
    {
        switch ($criteriaSlug) {
            case 'visited_url':
            case 'visited_path':
                return new PageVisitRelations;

            case 'device_is_mobile':
                return new BoolRelations;

            case 'device_manufacturer':
            case 'device_product':
            case 'os_name':
            case 'browser_name':
            case 'browser_language':
            case 'connection_ip_address':
            case 'location_country_name':
            case 'location_city_name':
            case 'location_subdivision_name':
            case 'email_domain':
            case 'birth_date':
            case 'search_term':
                return new TextRelations;

            case 'device_memory':
            case 'device_screen_width':
            case 'device_screen_height':
            case 'os_architecture':
            case 'os_version':
            case 'browser_version':
            case 'connection_bandwidth':
            case 'location_postal_code':
            case 'phone_provider':
            case 'created_at':
                return new NumberRelations;

            default: return new AllRelations;
        }
    }
}