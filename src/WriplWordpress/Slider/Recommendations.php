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
		$post = get_post();
		
		$catID = get_the_category( $post->ID );
		$selectCat = '';
		$catSelectTrue = false;
		
		if(isset($featureSettings['hideCatSliderSelect'])){
			$selectCat = $featureSettings['hideCatSliderSelect'];
		}
		
		foreach(($catID) as $category) {
			if($category->cat_ID == $selectCat && !(is_front_page())){
				$catSelectTrue = true;
			}
		}
		
        if (isset($featureSettings['sliderEnabled'])
		&&!(is_front_page() && isset($featureSettings['hideHomeSlider']) )
		&&!(isset($featureSettings['hideCatSlider']) && $catSelectTrue)) {
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