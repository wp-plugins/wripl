<?php

/**
 * Class WriplWordpress_SettingsPage
 *
 * Houses functionality relating to wripl's settings page
 */
class WriplWordpress_SettingsPage
{
    public static function registerHooks()
    {
        $plugin = WriplWordpress_Plugin::$instance;
        $pathToPluginFile = $plugin->getPathToPluginFile();

        add_action('admin_menu', array(__CLASS__, 'settingsPageMenu'));
        add_action('admin_init', array(__CLASS__, 'settingsPageInit'));
        add_filter('plugin_action_links_' . plugin_basename($pathToPluginFile), array(__CLASS__, 'pluginActionLinks'));
    }
    /**
     * Called by action hook admin_menu
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu
     */
    public static function settingsPageMenu()
    {
        add_options_page('Wripl Settings', 'Wripl', 'manage_options', 'wripl-settings', array(__CLASS__, 'settingsPage'));
    }

    /**
     * Called by action hook admin_init
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference/admin_init
     */
    public static function settingsPageInit()
    {
        register_setting('wripl_plugin_settings', 'wripl_settings');
        register_setting('wripl_plugin_features', 'wripl_feature_settings');
    }

    public static function pluginActionLinks($links)
    {
        return array_merge(
            array(
                'settings' => '<a href="' . get_bloginfo(
                        'wpurl'
                    ) . '/wp-admin/options-general.php?page=wripl-settings">Settings</a>'
            ),
            $links
        );
    }

    /**
     * Called by add_options_page in WriplWordpress_SettingsPage::settingsPageMenu
     */
    public static function settingsPage()
    {
        global $wpdb;
        $plugin = WriplWordpress_Plugin::$instance;

        $indexQueueTableName = WriplWordpress_Table_IndexQueue::getTableName();
        $settings = get_option('wripl_settings');
        $featureSettings = get_option('wripl_feature_settings');

        $setUp = WriplWordpress_Plugin::isSetup();

        /**
         * When the items are queued
         */
        if (array_key_exists('action', $_POST) && 'queueContent' === $_POST['action']) {
            self::queueUpItems();
            return;
        }

        $totalItemsInQueue = WriplWordpress_Table_IndexQueue::getTotalItemsInQueue();
        $totalItemsIndexed = WriplWordpress_Table_IndexQueue::getTotalItemsIndexed();

        include $plugin->getTemplatePath() . 'settings.php';
    }

    private static function queueUpItems()
    {
        error_log("queueing...");
        //6 hours
        set_time_limit(60 * 60 * 6);

        echo '<div class="icon32" id="icon-plugins"><br></div>';
        echo '<h2>Queuing content...</h2>';
        flush();

        global $wpdb;
        $indexQueueTableName = WriplWordpress_Table_IndexQueue::getTableName();

        $posts = get_posts(array('numberposts' => true));

        $totalPostCount = count($posts);
        $currentPostPosition = 0;

        foreach ($posts as $post) {

            echo '<p>post ' . ++$currentPostPosition . '/' . $totalPostCount . ' : ' . $post->post_title . '</p>';
            flush();

            if ('publish' === $post->post_status) {
                wp_clear_scheduled_hook('wripl_index_content', array($post->ID, 'post'));
                wp_schedule_single_event(time(), 'wripl_index_content', array($post->ID, 'post'));

                WriplWordpress_Table_IndexQueue::delete($post->ID);
                WriplWordpress_Table_IndexQueue::insert(
                    array(
                        'id' => $post->ID,
                        'type' => 'post',
                        'status' => WriplWordpress_Table_IndexQueue::ITEM_QUEUED
                    )
                );
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
                $wpdb->query("DELETE FROM $indexQueueTableName WHERE id = " . $page->ID);
                $wpdb->insert(
                    $indexQueueTableName,
                    array('id' => $page->ID, 'type' => 'page', 'status' => WriplWordpress_Table_IndexQueue::ITEM_QUEUED)
                );
            }
        }

        echo '<h4>done! <a href="' . get_bloginfo(
                'wpurl'
            ) . '/wp-admin/options-general.php?page=wripl-settings">back to wripl settings</a></h4>';
        flush();
    }
}
