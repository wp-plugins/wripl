<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

$wriplWP = WriplWP::$instance;

if (!$wriplWP->isSetup()) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit('wripl doesn\'t seem to be set up yet');
}

try {

    $wriplSettings = get_option('wripl_settings');

    $wriplApiBase = $wriplWP->wriplPluginHelper->getApiUrl();
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

} catch (Exception $e) {

    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

    if (isset($_GET['debug'])) {
        echo json_encode(array(
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ));
    }

    error_log('message : ' . $e->getMessage() . ' code: ' . $e->getCode());
}