<?php

class WriplWordpress_Plugin
{
    const VERSION = '1.6.0';

    static $instance;

    public $settings;

    protected $pathToPluginFile;

    public function __construct($pathToPluginFile)
    {
        self::$instance = $this;

        $this->pathToPluginFile = $pathToPluginFile;

        $this->settings = new WriplWordpress_Settings(dirname($pathToPluginFile) . '/developmentSettings.php');

        add_action('init', array($this, 'init'), 1);

        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        add_action('admin_notices', array($this, 'curlNotInstalledNotice'));

        WriplWordpress_ContentSync::registerHooks();
        WriplWordpress_Widget_Recommendation::registerHooks();
        WriplWordpress_Slider_Recommendations::registerHooks();
        WriplWordpress_EndOfContent_Recommendations::registerHooks();
        WriplWordpress_EndOfContent_MostEngaging::registerHooks();
        WriplWordpress_Installer::registerHooks();
        WriplWordpress_SettingsPage::registerHooks();
    }

    public function init()
    {
    }

    public function getPathToPluginFile()
    {
        return $this->pathToPluginFile;
    }

    public function getTemplatePath()
    {
        return dirname($this->pathToPluginFile) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    }

    /**
     * Adding required scripts
     */
    public function enqueueScripts()
    {
        if (!self::isSetup()) {
            return;
        }

        $featureSettings = get_option('wripl_feature_settings');
        $wriplSettings = get_option('wripl_settings');

        $consumerKey = $wriplSettings['consumerKey'];

        wp_enqueue_style('wripl-style', plugins_url('style.css', $this->getPathToPluginFile()), array(), self::VERSION);

        wp_enqueue_script(
            'handlebars.js',
            plugin_dir_url($this->getPathToPluginFile()) . 'js/dependencies/handlebars-1.0.0-rc.3.js'
        );

        wp_enqueue_script(
            'jquery-nail-thumb',
            plugin_dir_url($this->getPathToPluginFile()) . 'js/dependencies/jquery.nailthumb.1.1.js',
            array('jquery')
        );

        wp_enqueue_script('wripl-async-script-loader', plugin_dir_url($this->getPathToPluginFile()) . 'js/wripl-async-script-loader.js');

        wp_register_script('wripl-interest-monitor', plugin_dir_url($this->getPathToPluginFile()) . 'js/dependencies/wripl-compiled.js');

        wp_register_script(
            'wripl-anon-activity',
            plugin_dir_url($this->getPathToPluginFile()) . 'js/wripl-anon-activity.js',
            array(
                'jquery',
                'wripl-interest-monitor'
            ),
            self::VERSION
        );

        wp_register_script(
            'wripl-anon-init-recommendations',
            plugin_dir_url($this->getPathToPluginFile()) . 'js/anon-recommendations/init.js',
            array(
                'jquery',
                'wripl-anon-activity'
            ),
            self::VERSION
        );

        wp_register_script(
            'wripl-anon-init-mostEngaging',
            plugin_dir_url($this->getPathToPluginFile()) . 'js/wripl-anon-init-mostEngaging.js',
            array(
                'jquery',
                'wripl-anon-activity'
            ),
            self::VERSION
        );

        $wriplProperties = array(
            'apiBase' => $this->settings->getApiUrl(),
            'path' => self::getPathUri(),
            'pluginPath' => plugin_dir_url($this->getPathToPluginFile()),
            'pluginVersion' => self::VERSION,
            'key' => $consumerKey,
            'asyncScripts' => array(),
        );

        if (isset($featureSettings['hideWriplBranding'])) {
            $wriplProperties['hideWriplBranding'] = 'true';
        }

        wp_localize_script('wripl-anon-activity', 'WriplProperties', $wriplProperties);
    }

    function curlNotInstalledNotice()
    {
        if (!function_exists('curl_exec')) {
            echo '<div class="error"><p>Warning, wripl requires curl, specifically <em>curl_exec</em>, please enable or contact your server admin.</p></div>';
        }
    }

    public static function isSetup()
    {
        $options = get_option('wripl_settings');

        if ($options) {
            return isset($options['consumerKey']) && isset($options['consumerSecret']) ? true : false;
        }

        return false;
    }

    /**
     * @return string
     */
    public static function getPathUri()
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
}
