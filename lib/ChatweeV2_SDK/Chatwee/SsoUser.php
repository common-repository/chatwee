<?php

class ChatweeV2_SsoUser {

    public static function register($parameters) {
        $requestParameters = Array(
            "login" => $parameters["login"],
            "isAdmin" => $parameters["isAdmin"] === true ? "1" : "0",
            "avatar" => $parameters["avatar"]
        );
        $httpClient = new ChatweeV2_HttpClient();
        $httpClient->put("sso-user/register", $requestParameters);
        $userId = $httpClient->getResponseObject();
        return $userId;
    }

    public static function login($parameters) {
        $requestParameters = Array(
            "userId" => $parameters["userId"]
        );

        if(isSet($parameters["userIp"]))
        {
            if(filter_var($parameters["userIp"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
            {
                $requestParameters["userIp"] = $parameters["userIp"];
            }
        }

        $httpClient = new ChatweeV2_HttpClient();
        $httpClient->post("sso-user/login", $requestParameters);

        $sessionId = $httpClient->getResponseObject();
        return $sessionId;
    }

    public static function removeSession($parameters) {
        $requestParameters = Array(
            "sessionId" => $parameters["sessionId"]
        );

        $httpClient = new ChatweeV2_HttpClient();
        $httpClient->delete("sso-user/remove-session", $requestParameters);
    }

    public static function validateSession($parameters) {
        $requestParameters = Array(
            "sessionId" => $parameters["sessionId"]
        );

        $httpClient = new ChatweeV2_HttpClient();
        $httpClient->get("sso-user/validate-session", $requestParameters);

        $validationResponse = $httpClient->getResponseObject();
        return $validationResponse;
    }

    public static function logout($parameters) {
        $requestParameters = Array(
            "userId" => $parameters["userId"]
        );

        $httpClient = new ChatweeV2_HttpClient();
        $httpClient->post("sso-user/logout", $requestParameters);
    }

    public static function edit($parameters) {
        $requestParameters = Array(
            "userId" => $parameters["userId"],
            "login" => $parameters["login"],
            "avatar" => $parameters["avatar"]
        );

        if(isSet($parameters["isAdmin"]) === true) {
            $requestParameters["isAdmin"] = $parameters["isAdmin"] === true ? "1" : "0";
        }
        $httpClient = new ChatweeV2_HttpClient();
        $httpClient->post("sso-user/edit", $requestParameters);
    }
}
