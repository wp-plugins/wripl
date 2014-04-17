<?php

/**
 * Class WriplWordpress_ContentSync
 *
 * Sends content to wripl for analysis
 */
class WriplWordpress_ContentSync
{

    public static function registerHooks()
    {
        add_action('publish_post', array(__CLASS__, 'onPostPublish'));
        add_action('publish_page', array(__CLASS__, 'onPagePublish'));
        add_action('wp_trash_post', array(__CLASS__, 'onPostTrash'));
        add_action('wripl_index_content', array(__CLASS__, 'indexContent'), 5, 2);
    }

    /**
     * Called by action hook publish_post
     * @link http://codex.wordpress.org/Plugin_API/Action_Reference/publish_post
     *
     * @param $postId
     */
    public static function onPostPublish($postId)
    {
        wp_schedule_single_event(time(), 'wripl_index_content', array($postId, 'post'));

        WriplWordpress_Table_IndexQueue::delete($postId);
        WriplWordpress_Table_IndexQueue::insert(
            array(
                'id' => $postId,
                'type' => 'post',
                'status' => WriplWordpress_Table_IndexQueue::ITEM_QUEUED
            )
        );
    }

    /**
     * Called by action hook publish_page
     * @link http://codex.wordpress.org/Plugin_API/Action_Reference/publish_page
     *
     * @param $pageId
     */
    public static function onPagePublish($pageId)
    {
        wp_schedule_single_event(time(), 'wripl_index_content', array($pageId, 'page'));

        WriplWordpress_Table_IndexQueue::delete($pageId);
        WriplWordpress_Table_IndexQueue::insert(
            array(
                'id' => $pageId,
                'type' => 'page',
                'status' => WriplWordpress_Table_IndexQueue::ITEM_QUEUED
            )
        );
    }

    /**
     * This is called for *both* pages and posts. WTF!
     *
     * Called by action hook wp_trash_post
     *
     * @param $pId
     */
    public static function onPostTrash($pId)
    {
        $postOrPage = get_post($pId);

        switch ($postOrPage->post_type) {
            case 'post':
                $path = '?p=' . $postOrPage->ID;
                break;
            case 'page':
                $path = '?page_id=' . $postOrPage->ID;
                break;
            default:
                return;
                break;
        }

        try {
            $client = self::getWriplClient();
            $client->deleteFromIndex($path);
        } catch (Exception $e) {
            //Fail silently
        }

        wp_clear_scheduled_hook('wripl_index_content', array($pId, $postOrPage->post_type));
        WriplWordpress_Table_IndexQueue::delete($pId);
    }

    /**
     * @param $id Post Id
     * @param $type Post type
     */
    public function indexContent($id, $type)
    {
        $client = self::getWriplClient();

        $node = null;
        $tags = array();

        switch ($type) {
            case 'post':
                $node = get_post($id);
                $url = '?p=' . $id;

                $wpTags = get_the_tags($id);

                if ($wpTags) {
                    foreach ($wpTags as $wpTag) {
                        $tags[] = $wpTag->name;
                    }
                }

                break;
            case 'page':
                $node = get_page($id);
                $url = '?page_id=' . $id;

                break;

            default:
                return;
                break;
        }

        $publicationDate = new DateTime($node->post_date_gmt, new DateTimeZone('GMT'));

        $title = $node->post_title;
        $body = $node->post_content;
        $absoluteUrl = get_permalink($id);
        $imageUrl = null;

        $image = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'full');

        if ($image) {
            $imageUrl = $image[0];
        }

        try {
            $client->addToIndex($url, $title, $body, $absoluteUrl, $imageUrl, $tags, $publicationDate);
            WriplWordpress_Table_IndexQueue::update($id, array('status' => WriplWordpress_Table_IndexQueue::ITEM_INDEXED));
        } catch (Exception $e) {
            $error = array(
                'Exception' => get_class($e),
                'Message' => $e->getMessage(),
                'Code' => $e->getCode()
            );

            error_log(json_encode($error));

            /**
             * Queue up again on server error for 2 hours time in the event of an error.
             * If authentication fails, item will not be reattempted.
             */
            if ($e->getCode() === 500) {
                wp_schedule_single_event(time() + 7200, 'wripl_index_content', array($id, $type));
            }
        }
    }

    /**
     * @return Wripl_Client
     * @throws Exception
     */
    private static function getWriplClient()
    {
        $plugin = WriplWordpress_Plugin::$instance;
        /** @var WriplWordpress_Settings $settings */
        $settings = $plugin->settings;

        if (false == WriplWordpress_Plugin::isSetup()) {
            throw new Exception('Wripl not setup');
        }

        $wriplSettings = get_option('wripl_settings');

        $consumerKey = $wriplSettings['consumerKey'];
        $consumerSecret = $wriplSettings['consumerSecret'];

        $config['apiBaseUrl'] = $settings->getApiUrl();

        return new Wripl_Client(new Wripl_Oauth_Client_Adapter_OAuthSimple($consumerKey, $consumerSecret), $config);
    }
}
