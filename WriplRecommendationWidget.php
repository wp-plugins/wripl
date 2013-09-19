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
        'widgetFormat' => 'text',
        'imageHeight' => '90',
        'handleRecommendationsWithoutImages' => 'append'
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
        $widgetFormat = strip_tags($instance['widgetFormat']);
        $imageHeight = strip_tags($instance['imageHeight']);
        $handleRecommendationsWithoutImages = strip_tags($instance['handleRecommendationsWithoutImages']);

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

        <?php _e('Widget style:'); ?><br>
        <select class="widefat" id="<?php echo $this->get_field_id('widgetFormat'); ?>"
                name="<?php echo $this->get_field_name('widgetFormat') ?>">
            <option value="text" <?php selected($widgetFormat, 'text'); ?>>Just Links</option>
            <option value="withImages" <?php selected($widgetFormat, 'withImages'); ?>>Fancy Images</option>
        </select>

        <br><br>


        <?php
        if ($widgetFormat !== 'text'): ?>
            Image Height: (default: 90 pixels)<br>
            <input class="widefat"
                   type="text"
                   id="<?php echo $this->get_field_id('imageHeight'); ?>"
                   name="<?php echo $this->get_field_name('imageHeight'); ?>"
                   value="<?php echo $imageHeight; ?>"
                    >
            <p>
                and
                <select id="<?php echo $this->get_field_id('handleRecommendationsWithoutImages'); ?>"
                        name="<?php echo $this->get_field_name('handleRecommendationsWithoutImages') ?>">
                    <option value="append" <?php selected($handleRecommendationsWithoutImages, 'append'); ?>>append</option>
                    <option value="hide" <?php selected($handleRecommendationsWithoutImages, 'hide'); ?>>hide</option>
                </select>

                recommendations which have no featured image.
            </p>

        <?php endif ?>
    </p>

    <?php
    }

    function update($newInstance, $oldInstance)
    {
        $instance = array();
        //Popping the defaults into the old values
        $oldInstance = wp_parse_args((array)$oldInstance, $this->defaults);

        $instance['maxRecommendations'] = strip_tags($newInstance['maxRecommendations']);
        $instance['widgetFormat'] = strip_tags($newInstance['widgetFormat']);

        /**
         * If the new values are absent or invalid, then save the old ones.
         */
        $instance['imageHeight'] =  ((int) strip_tags($newInstance['imageHeight']) ? (int) strip_tags($newInstance['imageHeight']) : $oldInstance['imageHeight']);
        $instance['handleRecommendationsWithoutImages'] = strip_tags($newInstance['handleRecommendationsWithoutImages']) ? strip_tags($newInstance['handleRecommendationsWithoutImages']) : $oldInstance['handleRecommendationsWithoutImages'];

        return $instance;
    }

    public function widget($args, $instance)
    {
        $instance = wp_parse_args((array)$instance, $this->defaults);

        $imageFolderUrl = plugins_url('images', __FILE__);

        wp_enqueue_script('wripl-ajax-widget', plugin_dir_url(__FILE__) . 'js/widget-anon.js', array('jquery', 'handlebars.js'), WriplWP::VERSION);
        wp_localize_script('wripl-ajax-widget', 'WriplWidgetProperties', $instance);

        $title = 'Suggestions For You:';

        $out = $args['before_widget'];
        $out .= $args['before_title'] . $title . $args['after_title'];

        $out .= "<div id='wripl-widget-ajax-container'><img class='wripl-rotate' src='$imageFolderUrl/wripl-logo-rotate-orng-sml.png'></div>";

        $out .= $args['after_widget'];

        echo $out;
    }

}