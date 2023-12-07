<?php

namespace App\Services;

use GuzzleHttp\Client;

class GeocodeService
{ 
    public static function getLocation($latitude, $longitude)
    {
        $client = new Client();
        $apiKey = config('mapsapi.key');
        $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'query' => [
                'latlng' => "$latitude,$longitude",
                'key' => $apiKey,
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        if (!empty($data['results'])) {
            $addressComponents = $data['results'][0]['address_components'];
            $province = $city = $subdistrict = $village = null;
            foreach ($addressComponents as $component) {
                if (in_array('administrative_area_level_1', $component['types'])) {
                    $province = $component['long_name'];
                } elseif (in_array('administrative_area_level_2', $component['types'])) {
                    $city = $component['long_name'];
                } elseif (in_array('administrative_area_level_3', $component['types'])) {
                    $subdistrict = $component['long_name'];
                } elseif (in_array('administrative_area_level_4', $component['types'])) {
                    $village = $component['long_name'];
                }
            }
            return [
                'province' => $province,
                'city' => $city,
                'subdistrict' => $subdistrict,
                'village' => $village,
            ];
        }
        return null;
    }
}