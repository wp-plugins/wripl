<?php

/**
 * Description of Wripl_Oauth_Client_Adapter_Interface
 *
 * @author brian
 */
Interface Wripl_Oauth_Client_Adapter_Interface
{

    public function __construct($consumeKey, $consumerSecret);

    public function getRequestToken($requestTokenUrl, $callBackUrl);

    public function setAccessToken(Wripl_Oauth_Token $requestToken);

    public function getAccessToken($accessTokenUrl, $verifier = null);

    public function authorize($authorizeUrl);

    public function get($endpoint, array $params = array());

    public function post($endpoint, array $params = array());

    //public function put(array $params);

    //public function delete(array $params);

}