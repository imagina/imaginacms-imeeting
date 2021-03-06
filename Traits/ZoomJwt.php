<?php

namespace Modules\Imeeting\Traits;

use Illuminate\Support\Facades\Log;

trait ZoomJWT
{


    /**
     * GET API KEYS
     * GENERATE JWT Token
     */
    private function generateToken($dataRequest)
    {


        if(isset($dataRequest['providerConnections'])){
            $apiKey = $dataRequest['providerConnections']['apiKey'] ?? null;
            $apiSecret = $dataRequest['providerConnections']['apiSecret'] ?? null;
        }else{
            $apiKey =  env('ZOOM_API_KEY', '');
            $apiSecret =  env('ZOOM_API_SECRET', '');  
        }

        if(empty($apiKey) || empty($apiSecret))
            throw new \Exception('API KEYS NOT FOUND', 404);

        $payload = [
            'iss' => $apiKey,
            'exp' => strtotime('+1 minute'),
        ];

        return \Firebase\JWT\JWT::encode($payload, $apiSecret, 'HS256');
    }

    /**
    * GET Config Module
    */
    public function getConfig($route ='asgard.imeeting.config.zoom'){

        return config($route);       
    }

    /**
     * GET API URL
     */
    private function getApiUrl()
    {

        $config = $this->getConfig();
        return $config['apiUrl'];

    }

    /**
     * GET BASE REQUEST HEADER
     */
    private function getRequestWithHeaderToken($dataRequest)
    {

        $token = $this->generateToken($dataRequest);

        return \Illuminate\Support\Facades\Http::withHeaders([
            'authorization' => 'Bearer ' . $token,
            'content-type' => 'application/json',
        ]);

    }

    /**
     *  POST - REQUEST 
     *  @param String $path
     *  @param Array $body
     */
    public function requestPost(string $path, array $body = [],$dataRequest)
    {

        $url = $this->getApiUrl();
        $request = $this->getRequestWithHeaderToken($dataRequest);

        return $request->post($url . $path, $body);

    }
    
    /**
    * Convert Time
    * @param DateTime
    * @return time
    */
    public function convertTimeFormat(string $dateTime){

        $date = new \DateTime($dateTime);
        return $date->format('Y-m-d\TH:i:s');
    }

}