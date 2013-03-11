(function ($) {

    /**
     * Gets and renders the widget.
     */
//    var getWidget = function () {
//
//        jQuery.post(
//            WriplAjax.ajaxUrl,
//            {
//                action:'wripl-get-widget-recommendations',
//                maxRecommendations:WriplAjax.maxRecommendations
//            },
//            function (response) {
//                jQuery("div#wripl-ajax-container").html(response);
//            }
//        );
//    }
//
//    /**
//     * Fetches the activity code from the host site,
//     * on success it starts the tracker and gets the widget content.
//     */
//    var beginTracking = function () {
//
//        jQuery.post(
//            WriplAjax.ajaxUrl,
//            {
//                action:'wripl-get-activity-code',
//                path:WriplAjax.path
//            },
//            function (response) {
//
//                if (response.activityHashId && response.endpoint) {
//                    wripl.main(response);
//                }
//
//                if (response.piwikScript) {
//                    var script = document.createElement('script');
//                    script.type = 'text/javascript';
//                    script.src = response.piwikScript;
//
//                    jQuery("body").append(script);
//                }
//
//                getWidget();
//
//            }
//        );
//    }
//
//    jQuery(document).ready(function () {
//
//        if (typeof WriplAjax.path != 'undefined') {
//            beginTracking();
//        } else {
//            getWidget();
//        }
//    });

    /**
     * NEW
     */
    const INIT_DONE_EVENT = "wripl-ajax-init-done";


    jQuery(document).ready(function () {

        // Add listeners
        $("body").bind( INIT_DONE_EVENT , function (e, params) {
            console.log(e);
            console.log(params);
        });

        var source   = "<p>{{lastName}}, {{firstName}}</p>";
        var template = Handlebars.compile(source);
        var compiledhtml = template({ lastName : 'World', firstName : 'Hello' }); // (step 3)

        $('#wripl-recommentadion-widget-ajax-2').html(compiledhtml);
    });


})(jQuery);