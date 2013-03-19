<?php

class WriplPluginHelper
{

    protected $apiUrl;

    /**
     * @param $apiUrl
     */
    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @return string http|https
     */
    public function getCurrentProtocol()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
    }

    /**
     * @return string
     */
    public function getPathUri()
    {

        if (is_single() || is_page()) {

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

    /**
     * @return string Url to monitor script
     */
    public function getMonitorScriptUrl()
    {
        return Wripl_Client::getWebRootFromApiUrl($this->getApiUrl()) . '/js/wripl-compiled.js';
    }

    /**
     * @return string return the api url
     */
    public function getApiUrl()
    {
        $devSettingFile = dirname(__FILE__) . '/WriplWPDevSettings.php';

        if (file_exists($devSettingFile)) {
            require_once $devSettingFile;

            return WriplWPDevSettings::WRIPL_API_URL;
        }

        return $this->apiUrl;
    }

    /**
     * @param $recommendations
     * @return array
     */
    public static function sortRecommendations($recommendations)
    {
        $postIds = array();
        $pageIds = array();

        $itemOrder = array();

        /**
         * Collect post id's
         */
        foreach ($recommendations as $recommendation) {
            if (substr($recommendation->uri, 0, 3) === '?p=') {
                $id = substr($recommendation->uri, 3, strlen($recommendation->uri));
                $itemOrder[]['post'] = $id;
                $postIds[] = $id;
            } elseif (substr($recommendation->uri, 0, 9) === '?page_id=') {
                $id = substr($recommendation->uri, 9, strlen($recommendation->uri));
                $itemOrder[]['page'] = $id;
                $pageIds[] = $id;
                ;
            }
        }

        $posts = get_posts(array('include' => $postIds));
        $pages = get_pages(array('include' => $pageIds));

        $indexedPosts = array();
        foreach ($posts as $post) {
            $indexedPosts[$post->ID] = $post;
            $indexedPosts[$post->ID]->image = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
            $indexedPosts[$post->ID]->permalink = get_permalink($post->ID);
        }

        $indexedPages = array();
        foreach ($pages as $page) {
            $indexedPages[$page->ID] = $page;
            $indexedPages[$page->ID]->image = wp_get_attachment_image_src( get_post_thumbnail_id($page->ID), 'full' );
            $indexedPages[$page->ID]->permalink = get_permalink($page->ID);
        }

        $indexedItems = array();

        foreach ($itemOrder as $item) {
            if (array_key_exists('post', $item)) {
                $indexedItems[] = $indexedPosts[$item['post']];
            }
            if (array_key_exists('page', $item)) {
                $indexedItems[] = $indexedPages[$item['page']];
            }
        }

        return $indexedItems;
    }

    public function storeOauthRefererUrl($url)
    {
        setcookie('wripl-oauth-referer', $url, strtotime('+1 hour'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    public function retrieveOauthRefererUrl()
    {
        return $_COOKIE['wripl-oauth-referer'];
    }

    public function deleteOauthRefererUrl()
    {
        setcookie('wripl-oauth-referer', 'FALSE', strtotime('-1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
    }
}