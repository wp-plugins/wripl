<?php
/*
  Plugin Name: Wripl
  Description: Plugin to bring wripl's easy recommendations to Wordpress.
  Version: 1.5.0
  Author: Wripl
  Author URI: http://wripl.com
 */
set_include_path(dirname(__FILE__) . '/libs' . PATH_SEPARATOR . get_include_path());

require_once dirname(__FILE__) . '/WriplRecommendationWidget.php';
require_once dirname(__FILE__) . '/WriplPluginHelper.php';
require_once dirname(__FILE__) . '/WriplTokenStore.php';


//Conditional includes to avoid conflicts with other plugins.
if (!class_exists('OAuthSimple')) {
    require_once dirname(__FILE__) . '/libs/OAuthSimple/OAuthSimple.php';
}

$wriplWP = new WriplWP();

class WriplWP
{

    const ITEM_NEEDS_INDEXING = -1;
    const ITEM_QUEUED = 0;
    const ITEM_INDEXED = 1;
    const VERSION = '1.5.0';

    public $wriplPluginHelper;

    protected $wriplIndexQueueTableName = null;
    static $instance;
    protected $apiUrl = 'http://api.wripl.com/v0.1';

    protected $mobileDetect;

    public function __construct()
    {

        $this->wriplPluginHelper = new WriplPluginHelper($this->apiUrl);

        global $wpdb;

        $this->wriplIndexQueueTableName = $wpdb->prefix . "wripl_index_queue";

        add_action('init', array($this, 'init'), 1);
        add_action('admin_menu', array($this, 'settingsPageMenu'));
        add_action('admin_init', array($this, 'settingsPageInit'));
        add_action('wripl_index_content', array($this, 'indexContent'), 5, 2);
        add_action('widgets_init', create_function('', 'return register_widget("WriplRecommendationWidget");'));

        add_action('publish_post', array($this, 'onPostPublish'));
        add_action('wp_trash_post', array($this, 'onPostTrash'));
        add_action('publish_page', array($this, 'onPagePublish'));
        add_action('wp_trash_page', array($this, 'onPageTrash'));

        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));

        add_action('wp_ajax_nopriv_wripl-ajax-init', array($this, 'ajaxInit'));
        add_action('wp_ajax_wripl-ajax-init', array($this, 'ajaxInit'));

        add_action( 'admin_notices', array($this, 'curlNotInstalledNotice'));

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'pluginActionLinks'));
        add_filter('the_content', array($this, 'addRecommendationsToEndOfContent'));

        register_activation_hook(__FILE__, array($this, 'onInstall'));
        register_deactivation_hook(__FILE__, array($this, 'onUninstall'));

        /**
         * ugly singleton
         */
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
        if (substr($className, 0, 6) === 'Wripl_') {
            $requestedFile = dirname(__FILE__) . '/libs/' . str_replace("_", DIRECTORY_SEPARATOR, $className) . '.php';

            if (is_readable($requestedFile)) {
                require_once $requestedFile;
            }
        }
    }

    /**
     * Adding required scripts
     */
    public function enqueueScripts()
    {
        if (!$this->isSetup()) {
            return;
        }

        $featureSettings = get_option('wripl_feature_settings');

        $wriplSettings = get_option('wripl_settings');
        $consumerKey = $wriplSettings['consumerKey'];

        wp_register_script(
            'handlebars.js', //handle
            plugin_dir_url(__FILE__) . 'js/dependencies/handlebars-1.0.0-rc.3.js'
        );

        wp_enqueue_script('jquery-nail-thumb', plugin_dir_url(__FILE__) . 'js/dependencies/jquery.nailthumb.1.1.js', array('jquery'));
        wp_enqueue_script('handlebars.js');

        wp_enqueue_style('wripl-style', plugins_url('style.css', __FILE__), array(), self::VERSION);

        wp_enqueue_script('wripl-piwik-script', plugin_dir_url(__FILE__) . 'js/dependencies/piwik.js');
        wp_enqueue_script('wripl-piwik-tracking-code', "http://wripl.com/metrics/$consumerKey.js", array('wripl-piwik-script'));

        wp_enqueue_script('wripl-interest-monitor', plugin_dir_url(__FILE__) . 'js/dependencies/wripl-compiled.js');

        /*
         * Enqueue scripts and styles for the QR code dialog.
         */
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-effects-fade');
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');


        /**
         * Old user init
        wp_enqueue_script('wripl-ajax-properties', plugin_dir_url(__FILE__) . 'js/wripl-ajax-init.js', array('jquery', 'wripl-interest-monitor'));
        wp_localize_script('wripl-ajax-properties', 'WriplAjaxProperties', array(
        'ajaxUrl' => admin_url('admin-ajax.php', $this->wriplPluginHelper->getCurrentProtocol()),
        'path' => $this->wriplPluginHelper->getPathUri(),
        'pluginPath' => plugin_dir_url(__FILE__),
        'pluginVersion' => self::VERSION,
        ));
         **/

        wp_enqueue_script(
            'wripl-properties', plugin_dir_url(__FILE__) . 'js/wripl-anon-init.js',
            array(
                'jquery', 'wripl-interest-monitor'
            ),
            self::VERSION
        );

        wp_localize_script('wripl-properties', 'WriplProperties', array(
            'apiBase' => $this->wriplPluginHelper->getApiUrl(),
            'path' => $this->wriplPluginHelper->getPathUri(),
            'pluginPath' => plugin_dir_url(__FILE__),
            'pluginVersion' => self::VERSION,
            'key' => $consumerKey
        ));

        if (isset($featureSettings['sliderEnabled'])) {

            wp_enqueue_script('jquery-effects-slide');

            wp_enqueue_script('wripl-slider-container', plugin_dir_url(__FILE__) . 'js/slider-anon.js',
                array(
                    'jquery',
                    'jquery-effects-slide',
                    'jquery-nail-thumb',
                    'handlebars.js',
                ),
                self::VERSION
            );
        }

        if (isset($featureSettings['endOfContentEnabled'])) {
            wp_enqueue_script('wripl-end-of-content-container', plugin_dir_url(__FILE__) . 'js/endOfContent-anon.js',
                array(
                    'jquery',
                    'handlebars.js',
                ),
                self::VERSION
            );
        }
    }

    public function ajaxInit()
    {
        $response = array();
        $path = isset($_POST['path']) && !empty($_POST['path']) && $_POST['path'] !== 'null' ? $_POST['path'] : null;

        $accessToken = WriplTokenStore::retrieveAccessToken();

        if (is_null($accessToken)) {

            $response['piwikScript'] = $this->metricCollection(false, true);

            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);

        } else {

            $response['piwikScript'] = $this->metricCollection(true, true);

            // 1.) If a proper post, fetch activity code
            if (!is_null($path)) {

                try {
                    $client = $this->getWriplClient();
                    $result = $client->sendActivity($path, $accessToken->getToken(), $accessToken->gettokenSecret());

                    $resultDecoded = json_decode($result);

                    if (!$resultDecoded) {
                        throw new Exception();
                    }

                    $wriplApiBase = $this->wriplPluginHelper->getApiUrl();
                    $endpoint = $wriplApiBase . '/activity-update';

                    $response['activityHashId'] = $resultDecoded->activity_hash_id;
                    $response['endpoint'] = $endpoint;


                } catch (Exception $e) {
                    //Probably shouldn't crash out here...
                    //header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                    //echo $e->getMessage();
                    //exit;
                    $response['errors']['retrievingActivityCode'] = $e->getMessage();
                }
            }

            //2.) Get recommendations
            try {

                $recommendations = $this->requestRecommendations();

                $indexedItems = $this->wriplPluginHelper->sortRecommendations($recommendations);

                $response['recommendations'] = $indexedItems;

            } catch (Exception $e) {
                $response['errors']['retrievingRecommendations'] = $e->getMessage();
                $response['recommendations'] = array();
            }
        }

        header("Content-Type: application/json");
        echo json_encode($response);
        exit;
    }

    /**
     * NOTE!
     *
     * Tracking scripts for trial sites only.
     * Will be removed in future.
     *
     * @deprecated
     * @param bool $wriplEnabled
     */
    public function metricCollection($wriplEnabled = false, $return = false)
    {
        $wriplSettings = get_option('wripl_settings');

        $piwikWriplScript = "http://wripl.com/" . "metrics/{$wriplSettings['consumerKey']}.js";

        if ($wriplEnabled) {
            $piwikWriplScript .= '?wripl=on';
        }

        if ($return) {
            return $piwikWriplScript;
        }

        wp_enqueue_script('wripl-piwik-script', 'http://piwik.wripl.com/piwik.js');
        wp_enqueue_script('wripl-piwik-tracking-code', "$piwikWriplScript");
    }

    public function addRecommendationsToEndOfContent($content)
    {
        $featureSettings = get_option('wripl_feature_settings');

        if (isset($featureSettings['endOfContentEnabled']) && (is_single() || is_page())) {
            return $content . '<div id="wripl-end-of-content-container" class="wripl-ajax-container"></div>';
        }

        return $content;
    }

    public function pluginActionLinks($links)
    {
        return array_merge(
            array(
                'settings' => '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=wripl-settings">Settings</a>'
            ),
            $links);
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
        delete_option('wripl_feature_settings');

        $queuedItems = $wpdb->get_results("SELECT * FROM $this->wriplIndexQueueTableName WHERE status = " . self::ITEM_QUEUED);

        foreach ($queuedItems as $queuedItem) {
            wp_clear_scheduled_hook('wripl_index_content', array($queuedItem->id, $queuedItem->type));
        }

        $wpdb->query("DROP TABLE IF EXISTS $this->wriplIndexQueueTableName");
        $wpdb->query("DROP TABLE IF EXISTS $this->wriplTokenStoreTableName");
    }

    public function settingsPageInit()
    {
        register_setting('wripl_plugin_settings', 'wripl_settings');
        register_setting('wripl_plugin_features', 'wripl_feature_settings');
    }

    public function settingsPageMenu()
    {
        add_options_page('Wripl Settings', 'Wripl', 'manage_options', 'wripl-settings', array($this, 'settingsPage'));
    }

    public function settingsPage()
    {

        global $wpdb;

        $settings = get_option('wripl_settings');
        $featureSettings = get_option('wripl_feature_settings');
        $setUp = isset($settings['consumerKey']) && isset($settings['consumerSecret']) ? true : false;

        /**
         * When the items are queued
         */
        if (array_key_exists('action', $_POST) && 'queueContent' === $_POST['action']) {
            $this->queueUpItems();
            return;
        }

        $totalItemsInQueue = $wpdb->get_var("SELECT COUNT(*) FROM $this->wriplIndexQueueTableName");
        $totalItemsIndexed = $wpdb->get_var("SELECT COUNT(*) FROM $this->wriplIndexQueueTableName where status = " . self::ITEM_INDEXED);

        ?>
        <div class="wrap">

            <!-- Display Plugin Icon, Header, and Description -->
            <div class="icon32" id="icon-tools"><br></div>
            <h2>Wripl Setup</h2>

            <h3>Step 1 : Set wripl OAuth Keys</h3>

            <p>Here you can set your wripl tokens for secure communication with the wripl servers.</p>

            <p>If you don't haven credentials, contact me at <a href="mailto:brian@wripl.com">brian@wripl.com</a> and
                we'll
                get you set up.</p>
            <!-- Beginning of the Plugin Options Form -->
            <form method="post" action="options.php">
                <?php settings_fields('wripl_plugin_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Consumer Key</th>
                        <td>
                            <input type="text" size="57" name="wripl_settings[consumerKey]"
                                   value="<?php echo $settings['consumerKey']; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Consumer Secret</th>
                        <td>
                            <input type="text" size="57" name="wripl_settings[consumerSecret]"
                                   value="<?php echo $settings['consumerSecret']; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div/>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button-primary" value="Save Keys">
                </p>
            </form>

            <h3>Step 2 : Queue up content to send to wripl</h3>

            <p><em><?php echo $totalItemsIndexed ?>/<?php echo $totalItemsInQueue ?> content items sent...</em></p>

            <form method="post">
                <input type="hidden" name="action" value="queueContent">

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button-primary"
                           value="Queue Published Content" <?php echo $setUp ? '' : 'disabled="disabled"' ?>>
                </p>

            </form>

            <br/>

            <h3>Step 3 : Add the recommendation widget</h3>

            <p>You can now add the 'Wripl Recommendations' widget to your site <a
                    href="<?php echo get_admin_url() ?>widgets.php">here</a></p>

        </div>
        <br>

        <div class="wrap">
            <hr>
            <br>

            <div class="icon32" id="icon-themes"><br></div>
            <h2>Wripl Features</h2>

            <form method="post" action="options.php">
                <?php settings_fields('wripl_plugin_features'); ?>

                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">Enable Slider</th>
                        <td>
                            <label for="enableSlider">
                                <input id="enableSlider" type="checkbox" name="wripl_feature_settings[sliderEnabled]"
                                       value="1"<?php checked(isset($featureSettings['sliderEnabled'])); ?> />
                                Show the wripl recommendations in a slider as your users read your posts.
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable Recommendation at end of posts</th>
                        <td>
                            <label for="endOfContent">
                                <input id="endOfContent" type="checkbox" name="wripl_feature_settings[endOfContentEnabled]"
                                       value="1"<?php checked(isset($featureSettings['endOfContentEnabled'])); ?> />
                                Show the wripl recommendations at the end of your posts. <em>(Only works with posts containing a featured image)</em>
                            </label>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button-primary" value="Save wripl features">
                </p>

            </form>
        </div>

    <?php
    }

    public function queueUpItems()
    {
        error_log("queueing...");
        //6 hours
        set_time_limit(60 * 60 * 6);

        echo '<div class="icon32" id="icon-plugins"><br></div>';
        echo '<h2>Queuing content...</h2>';
        flush();

        global $wpdb;

        $posts = get_posts(array('numberposts' => true));

        $totalPostCount = count($posts);
        $currentPostPosition = 0;

        foreach ($posts as $post) {

            echo '<p>post ' . ++$currentPostPosition . '/' . $totalPostCount . ' : ' . $post->post_title . '</p>';
            flush();

            if ('publish' === $post->post_status) {
                wp_clear_scheduled_hook('wripl_index_content', array($post->ID, 'post'));
                wp_schedule_single_event(time(), 'wripl_index_content', array($post->ID, 'post'));
                $wpdb->query("DELETE FROM $this->wriplIndexQueueTableName WHERE id = " . $post->ID);
                $wpdb->insert($this->wriplIndexQueueTableName, array('id' => $post->ID, 'type' => 'post', 'status' => self::ITEM_QUEUED));
            }
        }

        $pages = get_pages(array('number' => true));

        $totalPageCount = count($pages);
        $currentPagePosition = 0;

        foreach ($pages as $page) {

            echo '<p>page ' . ++$currentPagePosition . '/' . $totalPageCount . ' : ' . $page->post_title . '</p>';
            flush();

            if ('publish' === $page->post_status) {
                wp_clear_scheduled_hook('wripl_index_content', array($page->ID, 'page'));
                wp_schedule_single_event(time(), 'wripl_index_content', array($page->ID, 'page'));
                $wpdb->query("DELETE FROM $this->wriplIndexQueueTableName WHERE id = " . $page->ID);
                $wpdb->insert($this->wriplIndexQueueTableName, array('id' => $page->ID, 'type' => 'page', 'status' => self::ITEM_QUEUED));
            }
        }

        echo '<h4>done! <a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=wripl-settings">back to wripl settings</a></h4>';
        flush();
    }

    function curlNotInstalledNotice() {
        if(!function_exists('curl_exec')) {
            echo '<div class="error"><p>Warning, wripl requires curl, specifically <em>curl_exec</em>, please enable or contact your server admin.</p></div>';
        }
    }

    public function isSetup()
    {
        $options = get_option('wripl_settings');

        if ($options) {
            return isset($options['consumerKey']) && isset($options['consumerSecret']) ? true : false;
        }

        return false;
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

        try {
            $client = $this->getWriplClient();
            $client->deleteFromIndex($path);
        } catch (Exception $e) {
            //fail silently
        }
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
        try {
            $client = $this->getWriplClient();
            $client->deleteFromIndex('page_id=' . $pageId);
        } catch (Exception $e) {
            //fail silently
        }

        global $wpdb;
        wp_clear_scheduled_hook('wripl_index_content', array($pageId, 'page'));
        $wpdb->query("DELETE FROM $this->wriplIndexQueueTableName WHERE id = " . $pageId);
    }

    /**
     * @param $id Post Id
     * @param $type Post type
     */
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
        $absoluteUrl = get_permalink($id);
        $imageUrl = null;

        $image = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'full');

        if ($image) {
            $imageUrl = $image[0];
        }

        $indexStatus = self::ITEM_INDEXED;

        try {

            $client->addToIndex($url, $title, $body, $absoluteUrl, $imageUrl, $tags, $publicationDate);
            $wpdb->update($this->wriplIndexQueueTableName, array('status' => self::ITEM_INDEXED), array('id' => $id));

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
    protected function getWriplClient()
    {
        if (!$this->isSetup()) {
            throw new Exception('Wripl not setup');
        }

        $wriplSettings = get_option('wripl_settings');

        $consumerKey = $wriplSettings['consumerKey'];
        $consumerSecret = $wriplSettings['consumerSecret'];

        $config['apiBaseUrl'] = $this->wriplPluginHelper->getApiUrl();

        return new Wripl_Client(new Wripl_Oauth_Client_Adapter_OAuthSimple($consumerKey, $consumerSecret), $config);
    }

    /**
     * @param int $max
     * @return Wripl_Link_Collection
     */
    public function requestRecommendations($max = 10)
    {
        $client = $this->getWriplClient();

        $accessToken = WriplTokenStore::retrieveAccessToken();

        return $client->getRecommendations($max, $accessToken->getToken(), $accessToken->getTokenSecret());
    }

}

?>