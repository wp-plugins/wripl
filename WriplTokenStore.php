<?php

class WriplTokenStore
{
    const WRIPL_OAUTH_REQUEST_TOKEN_COOKIE_KEY = 'wripl-ort';
    const WRIPL_OAUTH_ACCESS_TOKEN_COOKIE_KEY = 'wripl-oAt';

    public static function storeRequestToken(Wripl_Oauth_Token $requestToken)
    {
        setcookie(self::WRIPL_OAUTH_REQUEST_TOKEN_COOKIE_KEY, implode(':', array($requestToken->getToken(), $requestToken->getTokenSecret())), strtotime('+1 hour'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    public static function retrieveRequestToken()
    {
        if (isset($_COOKIE[self::WRIPL_OAUTH_REQUEST_TOKEN_COOKIE_KEY])) {
            $tokens = explode(':', $_COOKIE[self::WRIPL_OAUTH_REQUEST_TOKEN_COOKIE_KEY]);

            return new Wripl_Oauth_Token($tokens[0], $tokens[1]);
        }

        return null;
    }

    public static function deleteRequestToken()
    {
        setcookie(self::WRIPL_OAUTH_REQUEST_TOKEN_COOKIE_KEY, 'FALSE', strtotime('-1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    public static function storeAccessToken(Wripl_Oauth_Token $accessToken)
    {
        setcookie(self::WRIPL_OAUTH_ACCESS_TOKEN_COOKIE_KEY, ($accessToken->getToken() . ':' . $accessToken->getTokenSecret()), strtotime('+1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    public static function retrieveAccessToken()
    {
        if (isset($_COOKIE[self::WRIPL_OAUTH_ACCESS_TOKEN_COOKIE_KEY])) {
            $tokens = explode(':', $_COOKIE[self::WRIPL_OAUTH_ACCESS_TOKEN_COOKIE_KEY]);

            return new Wripl_Oauth_Token($tokens[0], $tokens[1]);
        }

        return null;
    }

    public static function deleteAccessToken()
    {
        setcookie(self::WRIPL_OAUTH_ACCESS_TOKEN_COOKIE_KEY, 'FALSE', strtotime('-1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

}