<?php

/**
 * Description of WriplRecommendationWidget
 *
 * @author brian
 */
class WriplRecommendationWidget extends WP_Widget
{

    protected $defaults = array(
        'maxRecommendations' => 5,
        'showImages' => '0'
    );

    public function WriplRecommendationWidget()
    {
        $widget_ops = array('classname' => 'wripl-widget-recommendation', 'description' => __('Displays wripl recommendations'));

        $this->WP_Widget('wripl-recommentadion-widget-ajax', __('Wripl Recommendations'), $widget_ops);
    }

    public function form($instance)
    {

        //  Assigns values
        $instance = wp_parse_args((array)$instance, $this->defaults);

        $maxRecommendations = strip_tags($instance['maxRecommendations']);
        $showImages = strip_tags($instance['showImages']);

        ?>

    <p xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
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

        <br><br>

        <label for="<?php echo $this->get_field_id('showImages'); ?>">
            <?php _e('Show Images:', 'wp_widget_plugin'); ?>
        </label>
        <input id="<?php echo $this->get_field_id('showImages'); ?>"
               name="<?php echo $this->get_field_name('showImages'); ?>"
               type="checkbox"
               value="1"
            <?php checked('1', $showImages); ?>
                />

    </p>

    <?php
    }

    function update($newInstance, $oldInstance)
    {
        $instance = $oldInstance;
        $instance['maxRecommendations'] = strip_tags($newInstance['maxRecommendations']);
        $instance['showImages'] = strip_tags($newInstance['showImages']);

        return $instance;
    }

    public function widget($args, $instance)
    {
        $instance = wp_parse_args((array)$instance, $this->defaults);

        $properties = array(
            'maxRecommendations' => $instance['maxRecommendations'],
            'showImages' => $instance['showImages'],
        );

        $imageFolderUrl = plugins_url('images', __FILE__);

        wp_enqueue_script('wripl-ajax-widget', plugin_dir_url(__FILE__) . 'js/widget.js', array('jquery', 'handlebars.js'), WriplWP::VERSION);
        wp_localize_script('wripl-ajax-widget', 'WriplWidgetProperties', $properties);

        $title = 'wripl recommends...';

        $out = $args['before_widget'];
        $out .= $args['before_title'] . $title . $args['after_title'];

        $out .= "<div id='wripl-widget-ajax-container'><img class='wripl-rotate' src='$imageFolderUrl/wripl-logo-rotate-orng-sml.png'></div>";

        $out .= $args['after_widget'];

        echo $out;
    }

}