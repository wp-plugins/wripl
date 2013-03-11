<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
require_once dirname(__FILE__) . '/libs/Wripl/Client.php';
require_once dirname(__FILE__) . '/libs/Wripl/Oauth/Token.php';
require_once dirname(__FILE__) . '/libs/Wripl/Oauth/Client/Adapter/OAuthSimple.php';

$wriplWP = WriplWP::$instance;

if (!$wriplWP->isSetup()) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit('wripl doesn\'t seem to be set up yet');
}

$wriplSettings = get_option('wripl_settings');

$wriplApiBase = $wriplWP->wriplPluginHelper->getApiUrl();
$consumerKey = $wriplSettings['consumerKey'];
$consumerSecret = $wriplSettings['consumerSecret'];

$config['apiBaseUrl'] = $wriplApiBase;
$config['oauthBaseUrl'] = Wripl_Client::getOauthUrlFromApiUrl($wriplApiBase);

$client = new Wripl_Client(new Wripl_Oauth_Client_Adapter_OAuthSimple($consumerKey, $consumerSecret), $config);

$requestToken = $wriplWP->retrieveRequestToken();

$client->setRequestToken($requestToken);

$accessToken = $client->getAccessToken();

$wriplWP->storeAccessToken($accessToken);
$wriplWP->nukeRequestToken();

$referer = $wriplWP->retrieveOauthRefererUrl();

header("Location: $referer");
?>
