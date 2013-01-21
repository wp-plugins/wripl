<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
//require_once dirname(__FILE__) . '/wripl.php';
//require_once dirname(__FILE__) . '/libs/OAuthSimple/OAuthSimple.php';
require_once dirname(__FILE__) . '/libs/Wripl/Client.php';
require_once dirname(__FILE__) . '/libs/Wripl/Oauth/Token.php';
require_once dirname(__FILE__) . '/libs/Wripl/Oauth/Client/Adapter/OAuthSimple.php';

$wriplWP = WriplWP::$instance;

if (!$wriplWP->isSetup()) {
    exit();
}

$wriplSettings = get_option('wripl_settings');

$wriplApiBase = $wriplWP->getApiUrl();
$consumerKey = $wriplSettings['consumerKey'];
$consumerSecret = $wriplSettings['consumerSecret'];

$config['apiBaseUrl'] = $wriplApiBase;
$config['oauthBaseUrl'] = Wripl_Client::getOauthUrlFromApiUrl($wriplApiBase);

$client = new Wripl_Client(new Wripl_Oauth_Client_Adapter_OAuthSimple($consumerKey, $consumerSecret), $config);

$requestToken = $wriplWP->retreiveRequestToken();

$client->setRequestToken($requestToken);

$accessToken = $client->getAccessToken();

$wriplWP->storeAccessToken($accessToken);
$wriplWP->nukeRequestToken();

$referer = $wriplWP->retreiveOauthRefererUrl();

header("Location: $referer");
?>
