<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
//require_once dirname(__FILE__) . '/wripl.php';
//require_once dirname(__FILE__) . '/libs/OAuthSimple/OAuthSimple.php';

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

/**
 * Hinky work around for a php bug on OSX Mountain Lion & apc (I think)
 * https://bugs.php.net/bug.php?id=60017&thanks=6
 */
//$callbackUrl = plugins_url() . '/' . basename(dirname((__FILE__))) . '/callback.php';
$callbackUrl = plugins_url('callback.php', __FILE__);
$requestToken = $client->getRequestToken($callbackUrl);

$wriplWP->storeRequestToken($requestToken);
$wriplWP->storeOauthRefererUrl($_SERVER['HTTP_REFERER']);

$client->authorize();
?>
