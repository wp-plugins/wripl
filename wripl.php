<?php
/*
  Plugin Name: Wripl
  Description: Pluging to bring wripl's easy recomendations.
  Version: 1.1
  Author: Brian Gallagher
  Author URI: http://wripl.com
 */
set_include_path(dirname(__FILE__) . '/libs' . PATH_SEPARATOR . get_include_path());

require_once dirname(__FILE__) . '/WriplRecommendationWidget.php';
//require_once dirname(__FILE__) . '/WriplRecommendationWidgetAjax.php';
require_once dirname(__FILE__) . '/libs/OAuthSimple/OAuthSimple.php';

$wriplWP = new WriplWP();

//$wriplWP->indexContent(2, 'page');

class WriplWP
{

    const ITEM_NEEDS_INDEXING = -1;
    const ITEM_QUEUED = 0;
    const ITEM_INDEXED = 1;

    protected $wriplIndexQueueTableName = null;
    static $instance;
    protected $apiUrl = 'http://api.wripl.com/v0.1';
    protected $wriplOauthAccessTokenCookieKey = 'wripl-oat';
    protected $wriplOauthRequestTokenCookieKey = 'wripl-ort';

    public function __construct()
    {
        global $wpdb;

        $this->wriplIndexQueueTableName = $wpdb->prefix . "wripl_index_queue";

        add_action('init', array($this, 'init'), 1);
        add_action('admin_menu', array($this, 'settingsPageMenu'));
        add_action('admin_init', array($this, 'settingsPageInit'));
        add_action('wripl_index_content', array($this, 'indexContent'), 5, 2);
        add_action('widgets_init', create_function('', 'return register_widget("WriplRecommendationWidget");'));
        //add_action('widgets_init', create_function('', 'return register_widget("WriplRecommendationWidgetAjax");'));
        add_action('wp_head', array($this, 'monitorInterests'));
        add_action('publish_post', array($this, 'onPostPublish'));
        add_action('wp_trash_post', array($this, 'onPostTrash'));
        add_action('publish_page', array($this, 'onPagePublish'));
        add_action('wp_trash_page', array($this, 'onPageTrash'));

        add_action('wp_ajax_nopriv_wripl-get-activity-code', array($this, 'ajaxActivityCode'));
        add_action('wp_ajax_wripl-get-activity-code', array($this, 'ajaxActivityCode'));

        add_action('wp_ajax_nopriv_wripl-get-widget-recommendations', array($this, 'ajaxWidgetRecommendationsHtml'));
        add_action('wp_ajax_wripl-get-widget-recommendations', array($this, 'ajaxWidgetRecommendationsHtml'));


        add_filter('the_content', array($this, 'dump'));

        register_activation_hook(__FILE__, array($this, 'onInstall'));
        register_deactivation_hook(__FILE__, array($this, 'onUninstall'));

        self::$instance = $this;
    }

    public function init()
    {
        spl_autoload_register(array($this, 'wriplAutoloader'));
    }

    /**
     * Autoloads PSR0 Wripl_* classes from ./libs/Wripl/
     * @param $className
     */
    public function wriplAutoloader($className)
    {
        if(substr($className, 0, 6) === 'Wripl_') {
            $requestedFile = dirname(__FILE__) . '/libs/' . str_replace("_" , DIRECTORY_SEPARATOR, $className) . '.php';

            if(is_readable($requestedFile)) {
                require_once $requestedFile;
            }
        }
    }

    /**
     * AJAX endpoint
     */
    public function ajaxWidgetRecommendationsHtml()
    {

        try {

            $recommendations = $this->requestRecommendations($_POST['maxRecommendations']);

            if (count($recommendations) !== 0) {
                $postIds = array();
                $pageIds = array();

                $itemOrder = array();

                /**
                 * Collect post id's
                 */
                foreach ($recommendations as $recommendation) {
                    if (substr($recommendation->uri, 0, 2) === 'p=') {
                        $id = substr($recommendation->uri, 2, strlen($recommendation->uri));
                        $itemOrder[]['post'] = $id;
                        $postIds[] = $id;
                    } elseif (substr($recommendation->uri, 0, 8) === 'page_id=') {
                        $id = substr($recommendation->uri, 8, strlen($recommendation->uri));
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
                }

                $indexedPages = array();
                foreach ($pages as $page) {
                    $indexedPosts[$page->ID] = $page;
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

                $out = '<ul>';

                foreach ($indexedItems as $item) {
                    $permalink = get_permalink($item->ID);

                    $out .= "<li><a href='$permalink'>$item->post_title</a></li>";
                }

                $out .= '</ul>';
            } else {
                $out .= "<p>Browse some content so wripl can see what you're into.</p>";
            }


            $connectUrl = plugins_url('disconnect.php', __FILE__);
            $out .= "<div id='wripl-oauth-disconnect-button'><a href='$interestUrl' target='_blank'>see your interests</a> | <a href='$connectUrl'>disconnect</a></div>";
        } catch (Exception $exc) {
            $out = "<p>it would seam something went wrong wih wripl</p>";
        }

        echo $out;
        exit;
    }

    /**
     * AJAX endpoint
     */
    public function ajaxActivityCode()
    {
        $path = isset($_POST['path']) ? $_POST['path'] : null;

        if (is_null($path)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
            exit;
        }

        $accessToken = $this->retreiveAccessToken();

        if (is_null($accessToken)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized', true, 401);
            exit;
        }

        try {
            $client = $this->getWriplClient();
            $result = $client->sendActivity($path, $accessToken->getToken(), $accessToken->gettokenSecret());

            $response = json_decode($result);

            $wriplApiBase = $this->getApiUrl();
            $response['endpoint'] = $wriplApiBase . '/activity-update';

            header("Content-Type: application/json");
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            exit;
        }
    }

    public function monitorInterests()
    {

        $wriplSettings = get_option('wripl_settings');
        $wriplApiBase = $this->getApiUrl();

        if (!$this->isSetup()) {
            return;
        }

        $css = plugins_url('style.css', __FILE__);
        echo "<link rel='stylesheet' href='$css' type='text/css' />";

        $accessToken = $this->retreiveAccessToken();

        //--- Begin metric collection ---\\

        /**
         * NOTE!
         *
         * Tracking scripts for trial sites only.
         * Will be removed in future.
         */
        $piwikScript = "http://piwik.wripl.com/piwik.js";
        $piwitWriplScript = "http://wripl.com/" . "metrics/{$wriplSettings['consumerKey']}.php";
        //$piwitWriplScript = Wripl_Client::getWebRootFromApiUrl($wriplApiBase) . "/metrics/{$wriplSettings['consumerKey']}.php";

        echo "<script type='text/javascript' src='$piwikScript'?wripl=off></script>";

        if (is_null($accessToken)) {
            echo "<script type='text/javascript' src='$piwitWriplScript'?wripl=off></script>";
            return;
        } else {
            echo "<script type='text/javascript' src='$piwitWriplScript?wripl=on'></script>";
        }

        //--- End metric collection ---\\

        if (is_null($accessToken)) {
            return;
        }


        /**
         * If its the home page we don't need to track
         * the user becase we don't know what exactly they are reading.
         */
        if (!is_single()) {
            return;
        }

        global $post;

        switch ($post->post_type) {
            case 'post':
                $path = '?p=' . $post->ID;
                break;
            case 'page':
                $path = '?page_id=' . $post->ID;
                break;
            default:
                return;
                break;
        }


        try {
            $client = $this->getWriplClient();
            $result = $client->sendActivity($path, $accessToken->getToken(), $accessToken->gettokenSecret());

            $activitiesResponse = json_decode($result);

            $script = Wripl_Client::getWebRootFromApiUrl($wriplApiBase) . '/js/wripl-compiled.js';
            $endpoint = $wriplApiBase . '/activity-update';

            echo "<script type='text/javascript' src='$script'></script>";
            echo "<script type='text/javascript'>wripl.main({activityHashId:'{$activitiesResponse->activity_hash_id}', 'endpoint' : '$endpoint'});</script>";
        } catch (Exception $e) {
            //var_dump($e);die;
        }
    }

    public function onInstall()
    {
        $indexQueueTableName = $this->wriplIndexQueueTableName;

        $indexQueueSql = "CREATE TABLE $indexQueueTableName (
            id bigint NOT NULL,
            type tinytext NOT NULL,
            status int NOT NULL
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($indexQueueSql);
    }

    public function onUninstall()
    {
        global $wpdb;

        delete_option('wripl_settings');

        $queuedItems = $wpdb->get_results("SELECT * FROM $this->wriplIndexQueueTableName WHERE status = " . self::ITEM_QUEUED);

        foreach ($queuedItems as $queuedItem) {
            wp_clear_scheduled_hook('wripl_index_content', array($queuedItem->id, $queuedItem->type));
        }

        $wpdb->query("DROP TABLE IF EXISTS $this->wriplIndexQueueTableName");
        $wpdb->query("DROP TABLE IF EXISTS $this->wriplTokenStoreTableName");
    }

    public function settingsPageMenu()
    {
        add_options_page('Wripl Settings', 'Wripl', 'manage_options', 'wripl-settings', array($this, 'settingsPage'));
    }

    public function isSetup()
    {
        $options = get_option('wripl_settings');

        if ($options) {
            return isset($options['consumerKey']) && isset($options['consumerSecret']) ? true : false;
        }

        return false;
    }

    public function settingsPage()
    {
        global $wpdb;

        $options = get_option('wripl_settings');
        $setUp = isset($options['consumerKey']) && isset($options['consumerSecret']) ? true : false;

        /**
         * When the items are queued
         */
        if (array_key_exists('action', $_POST) && 'queueContent' === $_POST['action']) {

            $posts = get_posts(array('numberposts' => true));

            foreach ($posts as $post) {

                if ('publish' === $post->post_status) {
                    wp_clear_scheduled_hook('wripl_index_content', array($post->ID, 'post'));
                    wp_schedule_single_event(time(), 'wripl_index_content', array($post->ID, 'post'));
                    $wpdb->query("DELETE FROM $this->wriplIndexQueueTableName WHERE id = " . $post->ID);
                    $wpdb->insert($this->wriplIndexQueueTableName, array('id' => $post->ID, 'type' => 'post', 'status' => self::ITEM_QUEUED));
                }
            }

            $pages = get_pages(array('number' => true));

            foreach ($pages as $page) {

                if ('publish' === $page->post_status) {
                    wp_clear_scheduled_hook('wripl_index_content', array($page->ID, 'page'));
                    wp_schedule_single_event(time(), 'wripl_index_content', array($page->ID, 'page'));
                    $wpdb->query("DELETE FROM $this->wriplIndexQueueTableName WHERE id = " . $page->ID);
                    $wpdb->insert($this->wriplIndexQueueTableName, array('id' => $page->ID, 'type' => 'page', 'status' => self::ITEM_QUEUED));
                }
            }
        }

        $totalItemsInQueue = $wpdb->get_var("SELECT COUNT(*) FROM $this->wriplIndexQueueTableName");
        $totalItemsIndexed = $wpdb->get_var("SELECT COUNT(*) FROM $this->wriplIndexQueueTableName where status = " . self::ITEM_INDEXED);
        ?>
        <div class="wrap">

            <!-- Display Plugin Icon, Header, and Description -->
            <div class="icon32" id="icon-plugins"><br></div>
            <h2>Wripl Settings</h2>
            <p>Below you can set your wripl tokens for secure communication with the wripl servers.</p>

            <h3>Step 1 : Set wripl OAuth Keys</h3>
            <!-- Beginning of the Plugin Options Form -->
            <form method="post" action="options.php">
                <?php settings_fields('wripl_plugin_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Consumer Key</th>
                        <td>
                            <input type="text" size="57" name="wripl_settings[consumerKey]" value="<?php echo $options['consumerKey']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Consumer Secret</th>
                        <td>
                            <input type="text" size="57" name="wripl_settings[consumerSecret]" value="<?php echo $options['consumerSecret']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div/>
                        </td>
                    </tr>
                </table>
                <input type="submit" name="submit" id="submit" class="button-primary" value="Save Settings">
            </form>

            <br/>
            <h3>Step 2 : Queue up content to send to wripl's api</h3>
            <p><em><?php echo $totalItemsIndexed ?>/<?php echo $totalItemsInQueue ?> content items sent...</em></p>
            <form method="post">
                <input type="submit" name="submit" id="submit" class="button-primary" value="Queue Published Content" <?php echo $setUp ? '' : 'disabled="disabled"' ?>>
                <input type="hidden" name="action" value="queueContent">
            </form>

            <br/>
            <h3>Step 3 : Add the recommendation widget</h3>
            <p>Add the 'Wripl Recommendations' widget to your site <a href="<?php echo get_admin_url() ?>widgets.php">here</a></p>

        </div>

        <?php
    }

    public function settingsPageInit()
    {
        register_setting('wripl_plugin_settings', 'wripl_settings');
    }

    public function onPostPublish($postId)
    {
        global $wpdb;
        wp_schedule_single_event(time(), 'wripl_index_content', array($postId, 'post'));
        $wpdb->query("DELETE FROM $this->wriplIndexQueueTableName WHERE id = " . $postId);
        $wpdb->insert($this->wriplIndexQueueTableName, array('id' => $postId, 'type' => 'post', 'status' => self::ITEM_QUEUED));
    }

    /**
     * This is called for both pages and posts. WTF!
     */
    public function onPostTrash($pId)
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


        $client = $this->getWriplClient();
        $client->deleteFromIndex($path);

        global $wpdb;
        wp_clear_scheduled_hook('wripl_index_content', array($pId, $postOrPage->post_type));
        $wpdb->query("DELETE FROM $this->wriplIndexQueueTableName WHERE id = " . $pId);
    }

    public function onPagePublish($pageId)
    {
        global $wpdb;
        wp_schedule_single_event(time(), 'wripl_index_content', array($pageId, 'page'));
        $wpdb->query("DELETE FROM $this->wriplIndexQueueTableName WHERE id = " . $pageId);
        $wpdb->insert($this->wriplIndexQueueTableName, array('id' => $pageId, 'type' => 'page', 'status' => self::ITEM_QUEUED));
    }

    public function onPageTrash($pageId)
    {
        die('page_id=' . $pageId);
        $client = $this->getWriplClient();
        $client->deleteFromIndex('page_id=' . $pageId);

        global $wpdb;
        wp_clear_scheduled_hook('wripl_index_content', array($pageId, 'page'));
        $wpdb->query("DELETE FROM $this->wriplIndexQueueTableName WHERE id = " . $pageId);
    }

    public function storeOauthRefererUrl($url)
    {
        setcookie('wripl-oauth-referer', $url, strtotime('+1 hour'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    public function retreiveOauthRefererUrl($url)
    {
        return $_COOKIE['wripl-oauth-referer'];
    }

    public function storeRequestToken(Wripl_Oauth_Token $requestToken)
    {
        setcookie($this->wriplOauthRequestTokenCookieKey, implode(':', array($requestToken->getToken(), $requestToken->getTokenSecret())), strtotime('+1 hour'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    public function retreiveRequestToken()
    {
        if (isset($_COOKIE[$this->wriplOauthRequestTokenCookieKey])) {
            $tokens = explode(':', $_COOKIE[$this->wriplOauthRequestTokenCookieKey]);

            return new Wripl_Oauth_Token($tokens[0], $tokens[1]);
        }

        return null;
    }

    public function nukeRequestToken()
    {
        setcookie($this->wriplOauthRequestTokenCookieKey, 'FALSE', strtotime('-1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
        setcookie('wripl-oauth-referer', 'FALSE', strtotime('-1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    public function storeAccessToken(Wripl_Oauth_Token $accessToken)
    {
        setcookie($this->wriplOauthAccessTokenCookieKey, ($accessToken->getToken() . ':' . $accessToken->getTokenSecret()), strtotime('+1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    public function retreiveAccessToken()
    {
        if (isset($_COOKIE[$this->wriplOauthAccessTokenCookieKey])) {
            $tokens = explode(':', $_COOKIE[$this->wriplOauthAccessTokenCookieKey]);

            return new Wripl_Oauth_Token($tokens[0], $tokens[1]);
        }

        return null;
    }

    public function nukeAccessToken()
    {
        setcookie($this->wriplOauthAccessTokenCookieKey, 'FALSE', strtotime('-1 year'), COOKIEPATH, COOKIE_DOMAIN, false);
    }

    public function dump($content)
    {
        return $content;
    }

    public function indexContent($id, $type)
    {
        global $wpdb;

        $client = $this->getWriplClient();

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

        try {
            $client->addToIndex($url, $title, $body, $tags, $publicationDate);
            $wpdb->update($this->wriplIndexQueueTableName, array('status' => self::ITEM_INDEXED), array('id' => $id));
        } catch (Exception $e) {
            error_log("index error : " . $e->getMessage());
            error_log("index error : " . $e->getTraceAsString());
            /**
             * Queue up again on fail for 2 hours time
             */
            //wp_schedule_single_event(time(), 'wripl_index_content', array($id, $type));
            wp_schedule_single_event(time() + 7200, 'wripl_index_content', array($id, $type));
        }
    }

    protected function getWriplClient()
    {
        if (!$this->isSetup()) {
            throw new Exception('Wripl not setup');
        }

        $wriplSettings = get_option('wripl_settings');

        $consumerKey = $wriplSettings['consumerKey'];
        $consumerSecret = $wriplSettings['consumerSecret'];

        $config['apiBaseUrl'] = $this->getApiUrl();

        return new Wripl_Client(new Wripl_Oauth_Client_Adapter_OAuthSimple($consumerKey, $consumerSecret), $config);
    }

    public function requestRecommendations($max = 10)
    {
        $client = $this->getWriplClient();

        $accessToken = $this->retreiveAccessToken();

        return $client->getRecommendations($max, $accessToken->getToken(), $accessToken->getTokenSecret());
    }

    public function getApiUrl()
    {
        $devSettingFile = dirname(__FILE__) . '/WriplWPDevSettings.php';

        if (file_exists($devSettingFile)) {
            require_once $devSettingFile;

            return WriplWPDevSettings::WRIPL_API_URL;
        }

        return $this->apiUrl;
    }

}
?>