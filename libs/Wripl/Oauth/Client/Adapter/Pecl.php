<?php

/**
 * Description of Wripl_Oauth_Client_Adapter_Pecl
 *
 * @author brian
 */
class Wripl_Oauth_Client_Adapter_Pecl implements Wripl_Oauth_Client_Adapter_Interface
{

    protected $callBackUrl = null;
    protected $oauth = null;
    protected $requestToken = null;

    /**
     *
     * @param type $consumeKey
     * @param type $consumerSecret
     */
    public function __construct($consumeKey, $consumerSecret)
    {
        $this->oauth = new OAuth($consumeKey, $consumerSecret);
    }

    public function getRequestToken($requestTokenUrl, $callBackUrl)
    {
        $requestTokens = $this->oauth->getRequestToken($requestTokenUrl, $callBackUrl);
        $this->requestToken = new Wripl_Oauth_Token($requestTokens['oauth_token'], $requestTokens['oauth_token_secret']);

        return $this->requestToken;
    }

    public function setRequestToken(Wripl_Oauth_Token $requestToken)
    {
        $this->requestToken = $requestToken;
        $this->oauth->setToken($requestToken->getToken(), $requestToken->gettokenSecret());
    }

    public function setAccessToken(Wripl_Oauth_Token $accessToken)
    {
        $this->oauth->setToken($accessToken->getToken(), $accessToken->gettokenSecret());
    }

    public function getAccessToken($accessTokenUrl, $verifier = null)
    {
        $accessTokensArray = $this->oauth->getAccessToken($accessTokenUrl);

        $accessToken = new Wripl_Oauth_Token($accessTokensArray['oauth_token'], $accessTokensArray['oauth_token_secret']);

        unset($accessTokensArray['oauth_token']);
        unset($accessTokensArray['oauth_token_secret']);

        foreach ($accessTokensArray as $key => $value) {
            $accessToken->$key = $value;
        }

        return $accessToken;
    }

    public function authorize($authorizeUrl)
    {
        header("Location: $authorizeUrl?oauth_token=" . $this->requestToken->getToken());
    }

    public function post($endpoint, array $params = array())
    {
        $this->oauth->fetch($endpoint, $params, OAUTH_HTTP_METHOD_POST);
        return $this->oauth->getLastResponse();
    }

    public function get($endpoint, array $params = array())
    {
        $this->oauth->fetch($endpoint, $params, OAUTH_HTTP_METHOD_GET);
        return $this->oauth->getLastResponse();
    }

    public function delete($endpoint, array $params = array())
    {
        $this->oauth->fetch($endpoint, $params, OAUTH_HTTP_METHOD_DELETE);
        return $this->oauth->getLastResponse();
    }


}