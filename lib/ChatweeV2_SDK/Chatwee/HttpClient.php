<?php

class ChatweeV2_HttpClient
{
    const USER_AGENT_HEADER = "ChatweeV2 PHP SDK 1.02 RAW";

    private $response;

    private $responseStatus;

    private $responseObject;

    public function get($path, $parameters)
    {
        try
        {
            if(ChatweeV2_Configuration::isConfigurationSet() === false)
            {
                throw new \Exception("The client credentials are not set");
            }
            $apiUrl = ChatweeV2_Configuration::getApiUrl();
            $parameters["chatId"] = ChatweeV2_Configuration::getChatId();
            $parameters["clientKey"] = ChatweeV2_Configuration::getClientKey();

            $serializedParameters = self::_serializeParameters($parameters);
            $url = $apiUrl . "/" . $path . "?" . $serializedParameters;

            self::call("GET", $url, null);
        }
        catch ( \RuntimeException $e )
        {
            if ( method_exists( get_parent_class(), __FUNCTION__ ) )
            {
                return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
            }
            else
            {
                throw $e;
            }
        }
    }


    public function delete($path, $parameters) {
        if(ChatweeV2_Configuration::isConfigurationSet() === false) {
            throw new Exception("The client credentials are not set");
        }

        $apiUrl = ChatweeV2_Configuration::getApiUrl();
        $parameters["chatId"] = ChatweeV2_Configuration::getChatId();
        $parameters["clientKey"] = ChatweeV2_Configuration::getClientKey();

        $url = $apiUrl . "/" . $path;
        self::call("DELETE", $url, $parameters);
    }

    public function post($path, $parameters) {
        if(ChatweeV2_Configuration::isConfigurationSet() === false) {
            throw new Exception("The client credentials are not set");
        }

        $apiUrl = ChatweeV2_Configuration::getApiUrl();
        $parameters["chatId"] = ChatweeV2_Configuration::getChatId();
        $parameters["clientKey"] = ChatweeV2_Configuration::getClientKey();

        $url = $apiUrl . "/" . $path;
        self::call("POST", $url, $parameters);
    }

    public function put($path, $parameters) {
        if(ChatweeV2_Configuration::isConfigurationSet() === false) {
            throw new Exception("The client credentials are not set");
        }

        $apiUrl = ChatweeV2_Configuration::getApiUrl();
        $parameters["chatId"] = ChatweeV2_Configuration::getChatId();
        $parameters["clientKey"] = ChatweeV2_Configuration::getClientKey();

        $url = $apiUrl . "/" . $path;
        self::call("PUT", $url, $parameters );
    }

    private function call($method, $url, $parameter) {
        $curl = curl_init();

        $customUserAgent = ChatweeV2_Configuration::getCustomUserAgent();
        $userAgent = $customUserAgent ? $customUserAgent : self::USER_AGENT_HEADER;

        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, Array (
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: ' . $userAgent
        ));

        if ($method != "GET") {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameter));
        }

        $this->response = curl_exec($curl);
        $this->responseStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $this->responseObject = $this->response ? json_decode($this->response) : null;

        if($this->responseStatus != 200) {
            $responseError = $this->responseObject ? $this->responseObject->errorMessage : "ChatweeV2 PHP SDK unknown error: " . $this->responseStatus;
            //$responseError .= ' ' . curl_error($curl);
            $responseError .= " (" . $url . ")";
            throw new Exception($responseError);
        }
    }

    private function _serializeParameters($parameters) {
        if(!is_array($parameters) || count($parameters) == 0) {
            return "";
        }

        $result = "";
        foreach($parameters as $key => $value) {
            $result .= ($key . "=" . ($value ? urlencode($value) : $value) . '&');
        }

        $result = substr_replace($result, "", -1);
        return $result;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getResponseObject() {
        return $this->responseObject;
    }

    public function getResponseStatus() {
        return $this->responseStatus;
    }
}
