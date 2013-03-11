<?php

/**
 * Description of WriplRecommendationWidget
 *
 * @author brian
 */
class WriplRecommendationWidget extends WP_Widget
{

    public function WriplRecommendationWidget()
    {
        $widget_ops = array('classname' => 'wripl_recommentadion_widget-ajax', 'description' => __('Displays wripl recommendations'));

        $this->WP_Widget('wripl-recommentadion-widget-ajax', __('Wripl Recommendations'), $widget_ops);
    }

    public function form($instance)
    {

        //  Assigns values
        $instance = wp_parse_args((array)$instance, array('maxRecommendations' => '5'));
        $maxRecommendations = strip_tags($instance['maxRecommendations']);
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('maxRecommendations'); ?>">
                <?php echo __('Max recommendations to display'); ?>:

            </label>
            <select class="widefat" id="<?php echo $this->get_field_id('maxRecommendations'); ?>"
                    name="<?php echo $this->get_field_name('maxRecommendations'); ?>">
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

        $properties['maxRecommendations'] = $instance['maxRecommendations'];

        $imageFolderUrl = plugins_url('images', __FILE__);

        wp_enqueue_script('wripl-ajax-widget', plugin_dir_url(__FILE__) . 'js/ajax-widget.js', array('jquery', 'handlebars.js'));
        wp_localize_script('wripl-ajax-widget', 'WriplWidgetProperties', $properties);

        $title = 'wripl recommends...';

        $out = $args['before_widget'];
        $out .= $args['before_title'] . $title . $args['after_title'];

        $out .= "<div id='wripl-ajax-container'><img class='wripl-rotate' src='$imageFolderUrl/wripl-logo-rotate-orng-sml.png'></div>";

        $out .= $args['after_widget'];

        echo $out;
    }

}