<?php

class WriplWordpress_Settings
{
    protected $settings = array(
        'apiUrl' => 'http://api.wripl.com/v0.1'
    );

    public function __construct($path = null)
    {
        if (file_exists($path)) {
            $settings = include $path;

            $this->settings = array_merge($this->settings, $settings);
        }
    }

    public function getApiUrl()
    {
        return $this->settings['apiUrl'];
    }
}
