<?php

class ChatweeV2_Session
{
	const SESSION_DURATION = 86400;

	private static function getCookieKey() {
		if(ChatweeV2_Configuration::isConfigurationSet() === false) {
			throw new Exception("The client credentials are not set");
		}
		return "chatwee-SID-" . ChatweeV2_Configuration::getChatId();
	}

    public static function getSessionId() {
    	$cookieKey = self::getCookieKey();
    	if (isSet($_COOKIE[$cookieKey]) && ChatweeV2_DataSanity::validateCookie($_COOKIE[$cookieKey])) {
            return sanitize_text_field($_COOKIE[$cookieKey]);
        }
    	return null;
    }

    public static function setSessionId($sessionId) {
		$hostChunks = explode(".", $_SERVER["HTTP_HOST"]);

		$hostChunks = array_slice($hostChunks, -2);

		$cookieDomain = "." . implode(".", $hostChunks);
        if (!is_object($sessionId)) {
            setcookie(self::getCookieKey(), $sessionId, time() + self::SESSION_DURATION, "/", $cookieDomain);
        }
    }

    public static function clearSessionId() {
		$hostChunks = explode(".", $_SERVER["HTTP_HOST"]);

		$hostChunks = array_slice($hostChunks, -2);

		$domain = "." . implode(".", $hostChunks);

		setcookie(self::getCookieKey(), "", time() - 1, "/", $domain);
    }

    public static function isSessionSet() {
		return ChatweeV2_Session::getSessionId() !== null;
    }
}
