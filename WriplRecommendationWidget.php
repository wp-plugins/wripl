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
        $widget_ops = array('classname' => 'wripl_recommentadion_widget', 'description' => __('Displays wripl recommendations'));

        $this->WP_Widget('wripl-recommentadion-widget', __('Wripl Recommendations'), $widget_ops);
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
        $wriplWP = WriplWP::$instance;
        $wriplWP->monitorInterests();

        $interestUrl = Wripl_Client::getWebRootFromApiUrl($wriplWP->getApiUrl()) . '/interests';
        $connectUrl = plugins_url('connect.php', __FILE__);
        $imageFolderUrl = plugins_url('images/', __FILE__);
        $title = 'wripl recommends...';

        $accessToken = $wriplWP->retreiveAccessToken();

        $wriplWP->metricCollection((bool) $accessToken);

        $out = $args['before_widget'];
        $out .= $args['before_title'] . 'wripl recommends...' . $args['after_title'];

        //When wripl isn't active
        if (is_null($accessToken)) {

            $out .= require dirname(__FILE__) . '/widget-template/default-deactivate.phtml';

        } //When wripl is active
        else {

            try {
                $recommendations = $wriplWP->requestRecommendations($instance['maxRecommendations']);
                $indexedItems = self::sortRecommendations($recommendations);

                if (count($recommendations) === 0) {
                    $out .= "<p>nothing right now, here's a random post to tide you over...</p>";

                    $indexedItems = query_posts('orderby=rand&posts_per_page=1');
                    wp_reset_query();
                }


                $out .= self::recommendationListHtml($indexedItems);


                $disconnectUrl = plugins_url('disconnect.php', __FILE__);
                $out .= "<div id='wripl-oauth-disconnect-button'><a href='$interestUrl' target='_blank'>see your interests</a> | <a href='$disconnectUrl'>disconnect</a></div>";
            } catch (Exception $exc) {
                $out = "<p>it would seam something went wrong wih wripl</p>";
            }
        }

        $out .= $args['after_widget'];
        echo $out;
    }

    public static function sortRecommendations($recommendations)
    {
        $postIds = array();
        $pageIds = array();

        $itemOrder = array();

        /**
         * Collect post id's
         */
        foreach ($recommendations as $recommendation) {
            if (substr($recommendation->uri, 0, 3) === '?p=') {
                $id = substr($recommendation->uri, 3, strlen($recommendation->uri));
                $itemOrder[]['post'] = $id;
                $postIds[] = $id;
            } elseif (substr($recommendation->uri, 0, 9) === '?page_id=') {
                $id = substr($recommendation->uri, 9, strlen($recommendation->uri));
                $itemOrder[]['page'] = $id;
                $pageIds[] = $id;
                ;
            }
        }

        $posts = get_posts(array('include' => $postIds));
        $pages = get_pages(array('include' => $pageIds));

        $indexedPosts = array();
        foreach ($posts as $post) {
            $indexedPosts[$post->ID] = $post;
        }

        $indexedPages = array();
        foreach ($pages as $page) {
            $indexedPages[$page->ID] = $page;
        }

        $indexedItems = array();

        foreach ($itemOrder as $item) {
            if (array_key_exists('post', $item)) {
                $indexedItems[] = $indexedPosts[$item['post']];
            }
            if (array_key_exists('page', $item)) {
                $indexedItems[] = $indexedPages[$item['page']];
            }
        }

        return $indexedItems;
    }

    public static function disconnectedHtml()
    {
        $connectUrl = plugins_url('connect.php', __FILE__);
        $imageFolderUrl = plugins_url('images', __FILE__);
        $title = 'wripl recommends...';

        $out = require dirname(__FILE__) . '/widget-template/default-deactivate.phtml';

        echo $out;
    }

    public static function recommendationListHtml($items)
    {
        $out = '<ul>';

        foreach ($items as $item) {
            $permalink = get_permalink($item->ID);

            $out .= "<li><a href='$permalink'>$item->post_title</a></li>";
        }

        $out .= '</ul>';
        return $out;

    }

}