<?php

/**
 * Description of WriplRecommendationWidget
 *
 * @author brian
 */
class WriplRecommendationWidgetAjax extends WP_Widget
{

    public function WriplRecommendationWidgetAjax()
    {
        $widget_ops = array('classname' => 'wripl_recommentadion_widget-ajax', 'description' => __('Displays wripl recommendations'));

        $this->WP_Widget('wripl-recommentadion-widget-ajax', __('Wripl AJAX Recommendations'), $widget_ops);
    }

    public function form($instance)
    {

        //  Assigns values
        $instance = wp_parse_args((array) $instance, array('maxRecommendations' => '10'));
        $maxRecommendations = strip_tags($instance['maxRecommendations']);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('maxRecommendations'); ?>">
                <?php echo __('Max recommendations to display'); ?>:

            </label>
            <select class="widefat" id="<?php echo $this->get_field_id('maxRecommendations'); ?>" name="<?php echo $this->get_field_name('maxRecommendations'); ?>">
                <?php
                for ($i = 1; $i <= 10; $i++) {

                    $selected = '';

                    if ($i == $maxRecommendations) {
                        $selected = ' selected="selected"';
                    }

                    echo "<option value='$i'$selected>$i</option>";
                }
                ?>
            </select>
        </p>
        <?php
    }

    function update($newInstance, $oldInstance)
    {

        $instance = $oldInstance;
        $instance['maxRecommendations'] = strip_tags($newInstance['maxRecommendations']);

        return $instance;
    }

    public function widget($args, $instance)
    {
        extract($args);
        $wriplWP = WriplWP::$instance;

        $wriplAjaxOptions['ajaxUrl'] = admin_url('admin-ajax.php');
        $wriplAjaxOptions['maxRecommendations'] = $instance['maxRecommendations'];

        if (is_single()) {
            global $post;

            switch ($post->post_type) {
                case 'post':
                    $wriplAjaxOptions['path'] = 'p=' . $post->ID;
                    break;
                case 'page':
                    $wriplAjaxOptions['path'] = 'page_id=' . $post->ID;
                    break;
                default:
                    return;
                    break;
            }
        }

        wp_enqueue_script('wripl-ajax-widget', plugin_dir_url(__FILE__) . 'js/ajax-widget.js', array('jquery'));
        wp_localize_script('wripl-ajax-widget', 'wriplAjax', $wriplAjaxOptions);

        echo $before_title . 'ajaxin...' . $after_title;
        echo $before_widget;

        $interestUrl = Wripl_Client::getWebRootFromApiUrl($wriplWP->getApiUrl()) . '/interests';

        $accessToken = $wriplWP->retreiveAccessToken();

        if (is_null($accessToken)) {
            $connectUrl = plugins_url('connect.php', __FILE__);
            echo "<div id='wripl-about'><p>Try out a new experimental service that suggests content just for you. <a href='http://wripl.com' target='_blank'>More info</a>.</p></div>";
            echo "<div id='wripl-oauth-connect-button'><a href='$connectUrl'>connect to wripl</a></div>";
        } else {

            $out = 'extra bleach';
        }

        echo $out;
        $connectUrl = plugins_url('disconnect.php', __FILE__);
        echo $after_widget;
    }

}