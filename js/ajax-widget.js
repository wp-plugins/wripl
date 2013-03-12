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

    jQuery(document).ready(function () {

        // Add listeners
        $("body").bind( "wripl-ajax-init-not-logged-in" , function (e, params) {
            console.log("Not logged in!");
            console.log(e);
            $.get( WriplAjaxProperties.pluginPath + 'handlebar-templates/recommendations-list-plugin-inactive.html', function(data) {

                var template = Handlebars.compile(data);
                var compiledHtml = template({ pluginPath: WriplAjaxProperties.pluginPath }); // (step 3)

                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });

        $("body").bind( "wripl-ajax-init-logged-in" , function (e, params) {
            console.log("Logged in!");
            console.log(params);

            $.get( WriplAjaxProperties.pluginPath + 'handlebar-templates/recommendations-list-plugin-active.html', function(data) {
                var template = Handlebars.compile(data);
                var compiledHtml = template({ pluginPath: WriplAjaxProperties.pluginPath, recommendations:params.recommendations }); // (step 3)
                $('#wripl-widget-ajax-container').html(compiledHtml);
            });

        });

    });




})(jQuery);