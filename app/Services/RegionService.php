<?php

namespace App\Services;

use GuzzleHttp\Client;

class RegionService
{ 
    public static function getProvince()
    {
        $baseUrl = config('custom.region_api_url');
        $client = new Client([
            'base_uri' => $baseUrl,
        ]);
        $response = $client->get('api/provinsi/get/'); 
        $data = json_decode($response->getBody(), true);
        return $data;
    }

    public static function getCity($idProvince)
    {
        $baseUrl = config('custom.region_api_url');
        $client = new Client([
            'base_uri' => $baseUrl,
        ]);
        $response = $client->get('api/kabkota/get/',[
            'query'=> [
                'd_provinsi_id' => $idProvince
            ]
        ]); 
        $data = json_decode($response->getBody(), true);
        return $data;
    }

    public static function getSubdistrict($idCity)
    {
        $baseUrl = config('custom.region_api_url');
        $client = new Client([
            'base_uri' => $baseUrl,
        ]);
        $response = $client->get('api/kecamatan/get/',[
            'query'=> [
                'd_kabkota_id' => $idCity
            ]
        ]); 
        $data = json_decode($response->getBody(), true);
        return $data;
    }

    public static function getVillage($idSubdistrict)
    {
        $baseUrl = config('custom.region_api_url');
        $client = new Client([
            'base_uri' => $baseUrl,
        ]);
        $response = $client->get('api/kelurahan/get/',[
            'query'=> [
                'd_kecamatan_id' => $idSubdistrict
            ]
        ]); 
        $data = json_decode($response->getBody(), true);
        return $data;
    }
}