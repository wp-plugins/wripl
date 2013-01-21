<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
//require_once dirname(__FILE__) . '/wripl.php';
//require_once dirname(__FILE__) . '/libs/OAuthSimple/OAuthSimple.php';
require_once dirname(__FILE__) . '/libs/Wripl/Client.php';
require_once dirname(__FILE__) . '/libs/Wripl/Oauth/Client/Adapter/OAuthSimple.php';

$referer = $_SERVER['HTTP_REFERER'];

$wriplWP = WriplWP::$instance;

$wriplWP->nukeAccessToken();

header("Location: $referer");

?>