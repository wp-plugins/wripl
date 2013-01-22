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
        $instance = wp_parse_args((array)$instance, array('maxRecommendations' => '10'));
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
        extract($args);
        $out = $before_widget;

        $wriplWP = WriplWP::$instance;

        $interestUrl = Wripl_Client::getWebRootFromApiUrl($wriplWP->getApiUrl()) . '/interests';
        $connectUrl = plugins_url('connect.php', __FILE__);
        $imageFolderUrl = plugins_url('images/', __FILE__);
        $title = 'wripl recommends...';

        $accessToken = $wriplWP->retreiveAccessToken();


        //When wripl isn't active
        if (is_null($accessToken)) {

            $out .= require dirname(__FILE__) . '/widget-template/default-deactivate.phtml';

//            $out .= '<div id="wripl-plugin" style="border: gray solid thin; height: 159; padding: 0px; margin: 8px 0px; overflow: auto; background-color: white">';
//            $out .= '<div id=\'wripl-header\' style=\'font-size: 1em; font-family: "Helvetica Neue", "Helvetica", "Arial"; padding: 6px; background-color: gainsboro; border-bottom: solid gray thin;\'>recommendations with wripl</div>';
//            $out .= '<div style="text-align: center; padding: 30px 0px;"><a href="' .$connectUrl  .' " style="text-decoration: none; border-bottom-width:0px;"><img src="' . $imageFolderUrl . 'connect-button.png" style="margin: 0 auto;" style="padding: 0px;"></a></div>';
//            $out .= '<div style="margin-left: 15px; margin-right: 15px; height: 2px; background-color: lightgrey;"></div>';
//            $out .= '<div style="padding: 8px 8px 0px 8px;">
//
//                <a href="https://www.twitter.com/sinkingfish" target="_blank" style="text-decoration: none; border-bottom-width:0px; margin-right: -2px;">
//                    <img src="' . $imageFolderUrl . 'twitter-user-1.png" style="padding: 0px; width:13%; ">
//                </a>
//                <a href="https://www.twitter.com/koidl" target="_blank" style="text-decoration: none; border-bottom-width:0px; margin-right: -2px;">
//                    <img src="' . $imageFolderUrl . 'twitter-user-2.png" style="padding: 0px; width:13%;">
//                </a>
//                <a href="https://www.twitter.com/robertross" target="_blank" style="text-decoration: none; border-bottom-width:0px; margin-right: -2px;">
//                    <img src="' . $imageFolderUrl . 'twitter-user-3.png" style="padding: 0px; width:13%;">
//                </a>
//                <a href="http://www.wripl.com" target="_blank" style="text-decoration: none;">
//                    <img src="' . $imageFolderUrl . 'wripl-logo.png" style="float: right; padding: 0px; width:45%;">
//                </a>
//            </div>';
//            $out .= '<div style="font-size: 0.9em; font-family: \'Helvetica Neue\', \'Helvetica\', \'Arial\'; padding: 4px 8px 8px 8px; ">
//                <span style="float: left; ">~3 users here</span>
//                <span style="float: right; padding-right: 2%; padding-bottom:6px;">wordpress plugin</span>
//            </div>';
//            $out .= '</div>';

        } //When wripl is active
        else {

            $out .= $before_title . 'wripl recommends...' . $after_title;

            try {
                $recommendations = $wriplWP->requestRecommendations($instance['maxRecommendations']);
                $recommendations = array();
                $indexedItems = $this->sortRecommendations($recommendations);

                if (count($recommendations) === 0) {
                    $out .= "<p>nothing right now, here's a random post to tide you over...</p>";

                    $indexedItems = query_posts('orderby=rand&posts_per_page=1');
                }


                $out .= '<ul>';

                foreach ($indexedItems as $item) {
                    $permalink = get_permalink($item->ID);

                    $out .= "<li><a href='$permalink'>$item->post_title</a></li>";
                }

                $out .= '</ul>';


                $disconnectUrl = plugins_url('disconnect.php', __FILE__);
                $out .= "<div id='wripl-oauth-disconnect-button'><a href='$interestUrl' target='_blank'>see your interests</a> | <a href='$disconnectUrl'>disconnect</a></div>";
            } catch (Exception $exc) {
                $out = "<p>it would seam something went wrong wih wripl</p>";
            }
        }

        $out .= $after_widget;
        echo $out;
    }

    public function sortRecommendations($recommendations)
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

}