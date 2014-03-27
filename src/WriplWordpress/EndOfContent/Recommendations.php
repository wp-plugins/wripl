<?php

class WriplWordpress_EndOfContent_Recommendations {

    public static function registerHooks()
    {
        add_filter('the_content', array(__CLASS__, 'addDivToDom'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueueScripts'));
    }

    public static function addDivToDom($content)
    {
        $featureSettings = get_option('wripl_feature_settings');

        if (isset($featureSettings['endOfContentEnabled']) && (is_single() || is_page())) {
            return $content . '<div id="wripl-end-of-content-recommendations-container" class="wripl-ajax-container wripl-end-of-content-container"></div>';
        }

        return $content;
    }

    public static function enqueueScripts()
    {
        $plugin = WriplWordpress_Plugin::$instance;
        $featureSettings = get_option('wripl_feature_settings');

        if (isset($featureSettings['endOfContentEnabled'])) {
            wp_enqueue_script(
                'wripl-end-of-content-recommendations',
                plugin_dir_url($plugin->getPathToPluginFile()) . 'js/anon-recommendations/endOfContent.js',
                array(
                    'jquery',
                    'handlebars.js',
                    'wripl-anon-init-recommendations',
                ),
                $plugin::VERSION
            );
        }
    }
}
