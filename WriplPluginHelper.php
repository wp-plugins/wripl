<?php

class WriplPluginHelper
{

    /**
     * @return string http|https
     */
    public static function getCurrentProtocol()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
    }

    public static function getPathUri()
    {
        if (is_single() && !is_page()) {
            global $post;

            switch ($post->post_type) {
                case 'post':
                    return '?p=' . $post->ID;
                    break;
                case 'page':
                    return '?page_id=' . $post->ID;
                    break;
                default:
                    return;
                    break;
            }
        }
    }
}
