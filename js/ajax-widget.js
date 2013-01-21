(function(){

    /**
     * Gets and renders the widget.
     */
    var getWidget = function() {

        jQuery.post(
            wriplAjax.ajaxUrl,
            {
                action: 'wripl-get-widget-recommendations'
            },
            function( response ) {
                alert(response);
            }
            );
    }

    /**
     * Fetches the activity code from the host site,
     * on success it starts the tracker and calls the callback if provided.
     */
    var beginTracking = function(callback){

        jQuery.post(
            wriplAjax.ajaxUrl,
            {
                action: 'wripl-get-activity-code',
                path: wriplAjax.path
            },
            function( response ) {

                wripl.main({activityHashId: response.activity_hash_id, endpoint : response.endpoint});

                if(typeof callback == 'function')
                {
                    callback();
                }

            }
            );
    }

    jQuery(document).ready(function() {

        if (typeof wriplAjax.path != 'undefined') {
            beginTracking(getWidget);
        }else{
            getWidget();
        }
    });

})();