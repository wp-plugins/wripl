<?php

class WriplWordpress_EndOfContent_MostEngaging
{
    public static function registerHooks()
    {
        add_filter('the_content', array(__CLASS__, 'addDivToDom'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueueScripts'));
    }

    public static function addDivToDom($content)
    {
        $featureSettings = get_option('wripl_feature_settings');

        if (isset($featureSettings['endOfContentMostEngagingEnabled']) && (is_single() || is_page())) {
            return $content . '<div id="wripl-end-of-content-container-most_engaging" class="wripl-ajax-container"></div>';
        }

        return $content;
    }

    public static function enqueueScripts()
    {
        $plugin = WriplWordpress_Plugin::$instance;
        $featureSettings = get_option('wripl_feature_settings');

        if (isset($featureSettings['endOfContentMostEngagingEnabled'])) {
            wp_enqueue_script(
                'wripl-end-of-content-mostEngaging',
                plugin_dir_url($plugin->getPathToPluginFile()) . 'js/endOfContent-anon-mostEngaging.js',
                array(
                    'jquery',
                    'handlebars.js',
                    'wripl-anon-init-mostEngaging'
                ),
                $plugin::VERSION
            );
        }
    }

}
