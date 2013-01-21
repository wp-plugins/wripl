<?php

/**
 * Adapter wrapper for OAuthSimple
 * @link https://github.com/jrconlin/oauthsimple/
 *
 * @author brian
 */
class Wripl_Oauth_Client_Adapter_OAuthSimple implements Wripl_Oauth_Client_Adapter_Interface
{

    protected $oAuthSimple = null;
    protected $requestToken = null;
    protected $accessToken = null;

    public function __construct($consumeKey, $consumerSecret)
    {
        if (!class_exists('OAuthSimple')) {
            require_once 'OAuthSimple.php';
        }

        $this->oAuthSimple = new OAuthSimple($consumeKey, $consumerSecret);
    }

    public function getRequestToken($requestTokenUrl, $callBackUrl)
    {
        $this->oAuthSimple
                ->setAction('get')
                ->setParameters(array('oauth_callback' => $callBackUrl))
                ->setPath($requestTokenUrl);

        $oauthSimpleSignature = $this->oAuthSimple->sign();

        $ch = curl_init($oauthSimpleSignature['signed_url']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: {$oauthSimpleSignature['header']}"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $responseBody = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);

        if (substr($responseInfo['http_code'], 0, 1) != 2) {
            throw new Wripl_Oauth_Client_Adapter_Exception($responseBody, $responseInfo['http_code']);
        }

        $returnedItems = array();

        parse_str($responseBody, $returnedItems);

        $this->requestToken = new Wripl_Oauth_Token($returnedItems['oauth_token'], $returnedItems['oauth_token_secret']);

        return $this->requestToken;
    }

    public function setRequestToken(Wripl_Oauth_Token $requestToken)
    {
        $this->requestToken = $requestToken;
    }

    public function authorize($authorizeUrl)
    {
        $authorizeUrl .= '?oauth_token=' . $this->requestToken->getToken();
        header('Location: ' . $authorizeUrl);
        exit;
    }

    public function getAccessToken($accessTokenUrl, $verifier = null)
    {
        $this->oAuthSimple
                ->reset()
                ->setTokensAndSecrets(array('oauth_token' => $this->requestToken->getToken(), 'oauth_secret' => $this->requestToken->getTokenSecret()))
                ->setParameters(array('oauth_verifier' => $_GET['oauth_verifier'], 'oauth_token' => $_GET['oauth_token']))
                ->setPath($accessTokenUrl);

        $oauthSimpleSignature = $this->oAuthSimple->sign();

        $ch = curl_init($oauthSimpleSignature['signed_url']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: {$oauthSimpleSignature['header']}"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $responseBody = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);

        if (substr($responseInfo['http_code'], 0, 1) != 2) {
            throw new Wripl_Oauth_Client_Adapter_Exception($responseBody, $responseInfo['http_code']);
        }

        $returnedItems = array();

        parse_str($responseBody, $returnedItems);

        $this->accessToken = new Wripl_Oauth_Token($returnedItems['oauth_token'], $returnedItems['oauth_token_secret']);

        unset($returnedItems['oauth_token']);
        unset($returnedItems['oauth_token_secret']);

        foreach ($returnedItems as $key => $value) {
            $this->accessToken->$key = $value;
        }

        return $this->accessToken;
    }

    public function setAccessToken(Wripl_Oauth_Token $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->oAuthSimple->setTokensAndSecrets(array('access_token' => $accessToken->getToken(), 'access_secret' => $accessToken->getTokenSecret()));
    }

    public function get($endpoint, array $params = array())
    {
        $this->oAuthSimple
                ->setAction('GET')
                ->setParameters($params)
                ->setPath($endpoint);

        $oauthSimpleSignature = $this->oAuthSimple->sign();

        $ch = curl_init($oauthSimpleSignature['signed_url']);

        curl_setopt_array($ch, Array(
            CURLOPT_HTTPHEADER => array("Authorization: {$oauthSimpleSignature['header']}"),
            CURLOPT_RETURNTRANSFER => true,
        ));

        $responseBody = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);

        //If it's not a 2xx then throw an exception with the response body
        if (substr($responseInfo['http_code'], 0, 1) != 2) {
            throw new Wripl_Oauth_Client_Adapter_Exception($responseBody, $responseInfo['http_code']);
        }

        return $responseBody;
    }

    public function post($endpoint, array $params = array())
    {
        $this->oAuthSimple
                ->setAction('POST')
                ->setParameters($params)
                ->setPath($endpoint);

        $oauthSimpleSignature = $this->oAuthSimple->sign(array('version' => '1.0a'));

        $ch = curl_init($endpoint);

        curl_setopt_array($ch, Array(
            CURLOPT_HTTPHEADER => array("Authorization: {$oauthSimpleSignature['header']}"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params
        ));

        $responseBody = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);

        //If it's not a 2xx then throw an exception with the response body
        if (substr($responseInfo['http_code'], 0, 1) != 2) {
            throw new Wripl_Oauth_Client_Adapter_Exception($responseBody, $responseInfo['http_code']);
        }

        return $responseBody;
    }

    public function delete($endpoint, array $params = array())
    {
        $this->oAuthSimple
                ->setAction('DELETE')
                ->setParameters($params)
                ->setPath($endpoint);

        $oauthSimpleSignature = $this->oAuthSimple->sign();

        $ch = curl_init($oauthSimpleSignature['signed_url']);

        curl_setopt_array($ch, Array(
            CURLOPT_HTTPHEADER => array("Authorization: {$oauthSimpleSignature['header']}"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ));

        $responseBody = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);

        //If it's not a 2xx then throw an exception with the response body
        if (substr($responseInfo['http_code'], 0, 1) != 2) {
            throw new Wripl_Oauth_Client_Adapter_Exception($responseBody, $responseInfo['http_code']);
        }

        return $responseBody;
    }

}