<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
require_once dirname(__FILE__) . '/WriplTokenStore.php';

$wriplWP = WriplWP::$instance;

if (!$wriplWP->isSetup()) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit('wripl doesn\'t seem to be set up yet');
}

$wriplSettings = get_option('wripl_settings');

$wriplApiBase = $wriplWP->wriplPluginHelper->getApiUrl();
$consumerKey = $wriplSettings['consumerKey'];
$consumerSecret = $wriplSettings['consumerSecret'];

$wriplApiConfig['apiBaseUrl'] = $wriplApiBase;
$wriplApiConfig['oauthBaseUrl'] = Wripl_Client::getOauthUrlFromApiUrl($wriplApiBase);

$client = new Wripl_Client(new Wripl_Oauth_Client_Adapter_OAuthSimple($consumerKey, $consumerSecret), $wriplApiConfig);

$requestToken = WriplTokenStore::retrieveRequestToken();

$client->setRequestToken($requestToken);

$accessToken = $client->getAccessToken();

WriplTokenStore::storeAccessToken($accessToken);
WriplTokenStore::deleteRequestToken();

$referer = $wriplWP->wriplPluginHelper->retrieveOauthRefererUrl();
$wriplWP->wriplPluginHelper->deleteOauthRefererUrl();
?>

<!doctype html>
<html>
    <body>
        <script>
            window.close();
        </script>
    </body>
</html>