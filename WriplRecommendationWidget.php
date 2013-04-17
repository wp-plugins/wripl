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
        'widgetFormat' => 'Text',
        'includeRecommendationsWithoutImages' => 'hide'
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
        $includeRecommendationsWithoutImages = strip_tags($instance['includeRecommendationsWithoutImages']);

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
            <option value="Text" <?php selected( $widgetFormat, 'Text' ); ?>>Text</option>
            <option value="Fancy Images" <?php selected( $widgetFormat, 'Fancy Images' ); ?>>Fancy Images</option>
        </select>

        <br><br>

        <span style="<?php if ( $widgetFormat === 'Text' ) echo 'display:none;'; ?>">
            <?php _e('and'); ?>
        </span>

        <select id="<?php echo $this->get_field_id('includeRecommendationsWithoutImages'); ?>"
                name="<?php echo $this->get_field_name('includeRecommendationsWithoutImages') ?>"
                style="<?php if ( $widgetFormat === 'Text' ) echo 'display:none;'; ?>">
            <option value="append" <?php selected( $includeRecommendationsWithoutImages, 'append' ); ?>>append</option>
            <option value="hide" <?php selected( $includeRecommendationsWithoutImages, 'hide' ); ?>>hide</option>
        </select>

        <span style="<?php if ( $widgetFormat === 'Text' ) echo 'display:none;'; ?>">
            <?php _e(' recommendations which have no featured image. '); ?>
        </span>

        <br><br>
    </p>

    <?php
    }

    function update($newInstance, $oldInstance)
    {
        $instance = $oldInstance;
        $instance['maxRecommendations'] = strip_tags($newInstance['maxRecommendations']);
        $instance['widgetFormat'] = strip_tags($newInstance['widgetFormat']);
        $instance['includeRecommendationsWithoutImages'] = strip_tags($newInstance['includeRecommendationsWithoutImages']);
        return $instance;
    }

    public function widget($args, $instance)
    {
        $instance = wp_parse_args((array)$instance, $this->defaults);

        $imageFolderUrl = plugins_url('images', __FILE__);

        wp_enqueue_script('wripl-ajax-widget', plugin_dir_url(__FILE__) . 'js/widget.js', array('jquery', 'handlebars.js'), WriplWP::VERSION);
        wp_localize_script('wripl-ajax-widget', 'WriplWidgetProperties', $instance);

        $title = 'wripl recommends...';

        $out = $args['before_widget'];
        $out .= $args['before_title'] . $title . $args['after_title'];

        $out .= "<div id='wripl-widget-ajax-container'><img class='wripl-rotate' src='$imageFolderUrl/wripl-logo-rotate-orng-sml.png'></div>";

        $out .= $args['after_widget'];

        echo $out;
    }

}