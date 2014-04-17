<?php

/**
 * Description of Wripl_Client
 *
 * @author brian
 */
class Wripl_Client
{

    protected $apiBaseUrl = null;
    protected $_oauthBaseUrl = null;
    protected $_consumerKey = null;
    protected $_consumerSecret = null;
    protected $config = null;
    protected $oauthAdapter = null;
    protected static $LINK_COLLECTION_ADAPTATION_ENDPOINT = 'link-collections';
    protected static $RECOMMENDATION_ENDPOINT = 'recommendations';
    protected static $PING_ENDPOINT = 'ping';
    protected static $DOCUMENTS_ENDPOINT = 'documents';
    protected static $ACTIVITY_ENDPOINT = 'activities';
    public static $defaultOauthUrls = array(
        'oauthBaseUrl' => 'http://oauth.wripl.com/v1.0a',
        'apiBaseUrl' => 'http://api.wripl.com/v0.1',
        'requestTokenEndpoint' => 'request_token',
        'authorizeEndpoint' => 'authorize',
        'accessTokenEndpoint' => 'access_token',
    );

    function __construct(Wripl_Oauth_Client_Adapter_Interface $oauthAdapter = null, array $config = array())
    {
        $this->oauthAdapter = $oauthAdapter;
        $this->config = array_merge(self::$defaultOauthUrls, $config);

        foreach ($this->config as &$value) {
            $value = rtrim($value,"/");
        }
    }

    public function getRequestToken($callbackUrl)
    {
        $requestTokenUrl = $this->config['oauthBaseUrl'] . '/' . $this->config['requestTokenEndpoint'];
        return $this->oauthAdapter->getRequestToken($requestTokenUrl, $callbackUrl);
    }

    public function setRequestToken(Wripl_Oauth_Token $requestToken)
    {
        $this->oauthAdapter->setRequestToken($requestToken);
    }

    public function getAccessToken($verifier = null)
    {
        $accessTokenUrl = $this->config['oauthBaseUrl'] . '/' . $this->config['accessTokenEndpoint'];
        return $this->oauthAdapter->getAccessToken($accessTokenUrl);
    }

    public function authorize()
    {
        $authorizeUrl = $this->config['oauthBaseUrl'] . '/' . $this->config['authorizeEndpoint'];
        $this->oauthAdapter->authorize($authorizeUrl);
    }

    /**
     *
     * @param Wripl_Link_Collection $linkCollection
     * @param String $token
     * @param String $secret
     * @return Wripl_Link_Collection
     */
    public function requestAdaptedLinkCollection(Wripl_Link_Collection $linkCollection, $token, $secret)
    {

        $endPointUrl = $this->config['apiBaseUrl'] . '/' . self::$LINK_COLLECTION_ADAPTATION_ENDPOINT;

        $this->oauthAdapter->setAccessToken(new Wripl_Oauth_Token($token, $secret));

        $params['link_collection'] = $linkCollection->toJson();

        $response = $this->oauthAdapter->post($endPointUrl, $params);

        $adaptedLinkCollectionJsonDecoded = json_decode($response);

        if (null === $adaptedLinkCollectionJsonDecoded) {
            throw new Wripl_Exception('Bad response from Wripl service. Could not decode response.');
        }

        return new Wripl_Link_Collection($adaptedLinkCollectionJsonDecoded);
    }

    /**
     *
     * @param String $token
     * @param tyStringpe $secret
     * @return Wripl_Link_Collection
     */
    public function getRecommendations($max, $token, $secret)
    {
        try {
            $endPointUrl = $this->config['apiBaseUrl'] . '/' . self::$RECOMMENDATION_ENDPOINT;

            $this->oauthAdapter->setAccessToken(new Wripl_Oauth_Token($token, $secret));

            $response = $this->oauthAdapter->get($endPointUrl, array('max' => $max));

            $linkCollectionJsonDecoded = json_decode($response);

            return new Wripl_Link_Collection($linkCollectionJsonDecoded);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @param String $path
     * @param String $token
     * @param String $secret
     * @return String Hash ID
     */
    public function sendActivity($path, $token, $secret)
    {
        $endPointUrl = $this->config['apiBaseUrl'] . '/' . self::$ACTIVITY_ENDPOINT;

        $this->oauthAdapter->setAccessToken(new Wripl_Oauth_Token($token, $secret));

        $params['path'] = $path;

        $response = $this->oauthAdapter->post($endPointUrl, $params);

        return $response;
    }

    /**
     * @param $path
     * @param $title
     * @param $body
     * @param $absoluteUrl
     * @param null $imageUrl
     * @param array $tags
     * @param DateTime $publicationDate
     * @return mixed
     */
    public function addToIndex($path, $title, $body, $absoluteUrl, $imageUrl = null, array $tags = array(), DateTime $publicationDate = null)
    {
        $endPointUrl = $this->config['apiBaseUrl'] . '/' . self::$DOCUMENTS_ENDPOINT;

        $params['path'] = $path;
        $params['title'] = $title;
        $params['body'] = $body;
        $params['absolute_url'] = $absoluteUrl;
        $params['tags'] = implode(',', $tags);

        if ($publicationDate) {
            $params['publication_date'] = $publicationDate->format(DateTime::ISO8601);
        }

        if($imageUrl) {
            $params['image_url'] = $imageUrl;
        }

        return $this->oauthAdapter->post($endPointUrl, $params);
    }

    public function deleteFromIndex($path)
    {
        $endPointUrl = $this->config['apiBaseUrl'] . '/' . self::$DOCUMENTS_ENDPOINT;
        $params['id'] = md5($path);

        return $this->oauthAdapter->delete($endPointUrl, $params);
    }

    /**
     * @return boolean Wheather or not the server resonded correctly.
     */
    public function ping()
    {
        $endPointUrl = $this->config['apiBaseUrl'] . '/' . self::$PING_ENDPOINT;

        $response = file_get_contents($endPointUrl);

        if ($response === 'pong') {
            return true;
        }
        return false;
    }

    /**
     * Returns the url for the oauth enpoint derived from the api url.
     *
     * @param string $baseApiUrl
     * @return string The url for the oauth enpoint derived from the api url.
     */
    public static function getOauthUrlFromApiUrl($baseApiUrl)
    {
        $baseApiUrlParts = parse_url($baseApiUrl);
        $hostParts = explode('.', $baseApiUrlParts['host']);

        //Get rid of subdomain
        unset($hostParts[0]);

        return 'http://oauth.' . implode('.', $hostParts) . '/v1.0a';
    }

    public static function getWebRootFromApiUrl($baseApiUrl)
    {
        $baseApiUrlParts = parse_url($baseApiUrl);
        $hostParts = explode('.', $baseApiUrlParts['host']);

        //Get rid of subdomain
        unset($hostParts[0]);

        return 'http://' . implode('.', $hostParts);
    }

}