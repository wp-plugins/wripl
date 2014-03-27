<?php

class WriplWordpress_Slider_Recommendations
{
    public static function registerHooks()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueueScripts'));
    }

    public static function enqueueScripts()
    {
        $featureSettings = get_option('wripl_feature_settings');
        $plugin = WriplWordpress_Plugin::$instance;

        if (isset($featureSettings['sliderEnabled'])) {
            wp_enqueue_script('jquery-effects-slide');

            wp_enqueue_script(
                'wripl-anon-slider-recommendations',
                plugin_dir_url($plugin->getPathToPluginFile()) . 'js/anon-recommendations/slider.js',
                array(
                    'jquery',
                    'jquery-effects-slide',
                    'jquery-nail-thumb',
                    'wripl-anon-init-recommendations',
                    'handlebars.js',
                ),
                WriplWordpress_Plugin::VERSION
            );
        }
    }

} 