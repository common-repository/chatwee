<?php

require("Chatwee/Configuration.php");
require("Chatwee/HttpClient.php");
require("Chatwee/SsoUser.php");
require("Chatwee/SsoManager.php");
require("Chatwee/Utils.php");
require("Chatwee/Session.php");

abstract class ChatweeV2 {}

if (version_compare(PHP_VERSION, "5.2.1", "<")) {
    throw new Exception("PHP version >= 5.2.1 required");
}


