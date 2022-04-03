<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoLocationService
{
    /**
     * @var Reader GeoIp2\Database\Reader
     */
    protected $reader;

    public function __construct()
    {
        try {
            $this->reader = new Reader(storage_path('app/GeoLite2-City.mmdb'));
        } catch (InvalidDatabaseException $e) {
            Log::error($e);
        }
    }

    /**
     * @param $ipAddress
     * @return \GeoIp2\Model\City
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
    public function getByIp(string $ipAddress)
    {
        $record = $this->reader->city($ipAddress);
        return $record;
    }

    /**
     * @param string $ipAddress
     * @param array $locationData optional
     * @return array
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
    public function getLocationData(string $ipAddress, array $locationData = null)
    {
        $geoData = $this->getByIp($ipAddress);
        return [
            'location_country_code'     => Arr::get($locationData, 'countryCode', $geoData->country->isoCode),
            'location_country_name'     => Arr::get($locationData, 'countryName', $geoData->country->name),
            'location_city_name'        => Arr::get($locationData, 'cityName', $geoData->city->name),
            'location_postal_code'      => Arr::get($locationData, 'postalCode', $geoData->postal->code),
            'location_subdivision_name' => Arr::get($locationData, 'subdivisionName', $geoData->mostSpecificSubdivision->name),
            'location_subdivision_code' => Arr::get($locationData, 'subdivisionCode', $geoData->mostSpecificSubdivision->isoCode),
            'location_latitude'         => Arr::get($locationData, 'latitude', $geoData->location->latitude),
            'location_longitude'        => Arr::get($locationData, 'longitude', $geoData->location->longitude)
        ];
    }
}
