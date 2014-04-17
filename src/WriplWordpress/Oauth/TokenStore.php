<?php

class WriplWordpress_Oauth_TokenStore
{
    const WRIPL_OAUTH_REQUEST_TOKEN_COOKIE_KEY = 'wripl-ort';
    const WRIPL_OAUTH_ACCESS_TOKEN_COOKIE_KEY = 'wripl-oat';

    /**
     * @param Wripl_Oauth_Token $requestToken
     */
    public static function storeRequestToken(Wripl_Oauth_Token $requestToken)
    {
        setcookie(self::WRIPL_OAUTH_REQUEST_TOKEN_COOKIE_KEY, implode(':', array($requestToken->getToken(), $requestToken->getTokenSecret())), strtotime('+1 hour'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    /**
     * @return null|Wripl_Oauth_Token
     */
    public static function retrieveRequestToken()
    {
        if (isset($_COOKIE[self::WRIPL_OAUTH_REQUEST_TOKEN_COOKIE_KEY])) {
            $tokens = explode(':', $_COOKIE[self::WRIPL_OAUTH_REQUEST_TOKEN_COOKIE_KEY]);

            return new Wripl_Oauth_Token($tokens[0], $tokens[1]);
        }

        return null;
    }

    /**
     * Deletes the request token stored in the cookie
     */
    public static function deleteRequestToken()
    {
        setcookie(self::WRIPL_OAUTH_REQUEST_TOKEN_COOKIE_KEY, 'FALSE', strtotime('-1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    /**
     * @param Wripl_Oauth_Token $accessToken
     */
    public static function storeAccessToken(Wripl_Oauth_Token $accessToken)
    {
        setcookie(self::WRIPL_OAUTH_ACCESS_TOKEN_COOKIE_KEY, ($accessToken->getToken() . ':' . $accessToken->getTokenSecret()), strtotime('+1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    /**
     * @return null|Wripl_Oauth_Token
     */
    public static function retrieveAccessToken()
    {

        if (isset($_COOKIE[self::WRIPL_OAUTH_ACCESS_TOKEN_COOKIE_KEY])) {
            $tokens = explode(':', $_COOKIE[self::WRIPL_OAUTH_ACCESS_TOKEN_COOKIE_KEY]);

            return new Wripl_Oauth_Token($tokens[0], $tokens[1]);
        }

        return null;
    }

    /**
     * Deletes the access token stored in the cookie
     */
    public static function deleteAccessToken()
    {
        setcookie(self::WRIPL_OAUTH_ACCESS_TOKEN_COOKIE_KEY, 'FALSE', strtotime('-1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
    }
}
