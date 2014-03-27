<div class="wrap">

    <!-- Display Plugin Icon, Header, and Description -->
    <div class="icon32" id="icon-tools"><br></div>
    <h2>Wripl Setup</h2>

    <h3>Step 1 : Set wripl OAuth Keys</h3>

    <p>Here you can set your wripl tokens for secure communication with the wripl servers.</p>

    <p>To get your wripl keys go to <a href="http://wripl.com/register" target="_blank">http://wripl.com/register</a>
        and register your site.</p>
    <!-- Beginning of the Plugin Options Form -->
    <form method="post" action="options.php">
        <?php settings_fields('wripl_plugin_settings'); ?>
        <table class="form-table">
            <tr>
                <th scope="row">Consumer Key</th>
                <td>
                    <input type="text" size="57" name="wripl_settings[consumerKey]"
                           value="<?php echo $settings['consumerKey']; ?>"/>
                </td>
            </tr>
            <tr>
                <th scope="row">Consumer Secret</th>
                <td>
                    <input type="text" size="57" name="wripl_settings[consumerSecret]"
                           value="<?php echo $settings['consumerSecret']; ?>"/>
                </td>
            </tr>
            <tr>
                <td>
                    <div/>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button-primary" value="Save Keys">
        </p>
    </form>

    <h3>Step 2 : Queue up content to send to wripl</h3>

    <p><em><?php echo $totalItemsIndexed ?>/<?php echo $totalItemsInQueue ?> content items sent...</em></p>

    <form method="post">
        <input type="hidden" name="action" value="queueContent">

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button-primary"
                   value="Queue Published Content" <?php echo $setUp ? '' : 'disabled="disabled"' ?>>
        </p>

    </form>

    <br/>

    <h3>Step 3 : Add the recommendation widget</h3>

    <p>You can now add the 'Wripl Recommendations' widget to your site <a
            href="<?php echo get_admin_url() ?>widgets.php">here</a></p>

</div>
<br>

<div class="wrap">
    <hr>
    <br>

    <div class="icon32" id="icon-themes"><br></div>
    <h2>Wripl Features</h2>

    <form method="post" action="options.php">
        <?php settings_fields('wripl_plugin_features'); ?>

        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">Enable Slider</th>
                <td>
                    <label for="enableSlider">
                        <input id="enableSlider" type="checkbox" name="wripl_feature_settings[sliderEnabled]"
                               value="1"<?php checked(isset($featureSettings['sliderEnabled'])); ?> />
                        Show the wripl recommendations in a slider as your users read your posts.
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">Enable Recommendation at end of posts</th>
                <td>
                    <label for="endOfContent">
                        <input id="endOfContent" type="checkbox"
                               name="wripl_feature_settings[endOfContentEnabled]"
                               value="1"<?php checked(isset($featureSettings['endOfContentEnabled'])); ?> />
                        Show the wripl recommendations at the end of your posts. <em>(Only works with posts
                            containing a featured image).</em>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">Enable Most Engaging at end of posts</th>
                <td>
                    <label for="endOfContentMostEngaging">
                        <input id="endOfContentMostEngaging" type="checkbox"
                               name="wripl_feature_settings[endOfContentMostEngagingEnabled]"
                               value="1"<?php checked(isset($featureSettings['endOfContentMostEngagingEnabled'])); ?> />
                        Show the most engaging posts at the end of your posts. <em>(Only works with posts
                            containing a featured image).</em>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">Hide wripl branding?</th>
                <td>
                    <label for="hideWriplBranding">
                        <input id="hideWriplBranding" type="checkbox"
                               name="wripl_feature_settings[hideWriplBranding]"
                               value="1"<?php checked(isset($featureSettings['hideWriplBranding'])); ?> />
                        Hide the 'powered by wripl' at the bottom of the recommendations.
                    </label>
                </td>
            </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button-primary" value="Save wripl features">
        </p>

    </form>
</div>