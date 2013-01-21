<?php

/**
 * Description of Token
 *
 * @author brian
 */
class Wripl_Oauth_Token
{
    protected $token;

    protected $tokenSecret;

    public function __construct($token = null, $tokenSecret = null)
    {
        $this->token = $token;
        $this->tokenSecret = $tokenSecret;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($value)
    {
        $this->token = $value;
    }
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }

    public function setTokenSecret($value)
    {
        $this->tokenSecret = $value;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        if (isset($this->name)) {
            return $this->name;
        }

        return null;
    }
}