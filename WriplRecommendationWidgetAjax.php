<?php

require_once dirname(__FILE__) . '/WriplRecommendationWidget.php';

/**
 * Description of WriplRecommendationWidget
 *
 * @author brian
 */
class WriplRecommendationWidgetAjax extends WriplRecommendationWidget
{

    public function WriplRecommendationWidgetAjax()
    {
        $widget_ops = array('classname' => 'wripl_recommentadion_widget-ajax', 'description' => __('Displays wripl recommendations'));

        $this->WP_Widget('wripl-recommentadion-widget-ajax', __('Wripl Recommendations (AJAX)'), $widget_ops);
    }

    public function widget($args, $instance)
    {
        $wriplWP = WriplWP::$instance;

        $imageFolderUrl = plugins_url('images', __FILE__);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";

        $wriplAjaxOptions['ajaxUrl'] = admin_url('admin-ajax.php', $protocol);
        $wriplAjaxOptions['maxRecommendations'] = $instance['maxRecommendations'];

        if (is_single() && !is_page()) {
            global $post;

            switch ($post->post_type) {
                case 'post':
                    $wriplAjaxOptions['path'] = '?p=' . $post->ID;
                    break;
                case 'page':
                    $wriplAjaxOptions['path'] = '?page_id=' . $post->ID;
                    break;
                default:
                    return;
                    break;
            }
        }

        wp_enqueue_script('wripl-interest-monitor', $wriplWP->getMonitorScriptUrl(), array('jquery'));
        wp_enqueue_script('wripl-ajax-widget', plugin_dir_url(__FILE__) . 'js/ajax-widget.js', array('jquery'));
        wp_enqueue_script('wripl-piwik-script', 'http://piwik.wripl.com/piwik.js');
        wp_localize_script('wripl-ajax-widget', 'WriplAjax', $wriplAjaxOptions);

        $title = 'wripl recommends...';

        $out = $args['before_widget'];
        $out .= $args['before_title'] . $title . $args['after_title'];

        $out .= "<div id='wripl-ajax-container'><img class='wripl-rotate' src='$imageFolderUrl/wripl-logo-rotate-orng-sml.png'></div>";

        $out .= $args['after_widget'];

        echo $out;
    }

}