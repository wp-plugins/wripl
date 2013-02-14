(function(){

    /**
     * Gets and renders the widget.
     */
    var getWidget = function() {

        jQuery.post(
            wriplAjax.ajaxUrl,
            {
                action: 'wripl-get-widget-recommendations',
                maxRecommendations: wriplAjax.maxRecommendations
            },
            function( response ) {

                jQuery("div#wripl-ajax-container").html(response);
            }
            );
    }

    /**
     * Fetches the activity code from the host site,
     * on success it starts the tracker and gets the widget content.
     */
    var beginTracking = function(){

        jQuery.post(
            wriplAjax.ajaxUrl,
            {
                action: 'wripl-get-activity-code',
                path: wriplAjax.path
            },
            function( response ) {

                if(response.activity_hash_id && response.endpoint) {
                    wripl.main({activityHashId: response.activity_hash_id, endpoint : response.endpoint});
                }

                getWidget();

            }
            );
    }

    jQuery(document).ready(function() {

        if (typeof wriplAjax.path != 'undefined') {
            beginTracking();
        }else{
            getWidget();
        }
    });

})();