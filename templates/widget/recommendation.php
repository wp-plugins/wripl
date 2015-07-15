<?php 
	$featureSettings = get_option('wripl_feature_settings');
if (isset($featureSettings['hideHomeSlider'])) {
           echo '';
        }

	?>
<?php echo $args['before_widget']; ?>



<?php echo $args['before_title']; ?> Suggestions For You: <?php echo $args['after_title'] ?>

<div id='wripl-widget-container' class='wripl-ajax-container'><img class='wripl-rotate' src='<?php echo $imageFolderUrl ?>/wripl-logo-rotate-orng-sml.png'></div>

<?php echo $args['after_widget']; ?>