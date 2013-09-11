console.log('widget-anon.js');
(function ($, Handlebars) {

    var events = {
        'INIT_EVENT': 'wripl-anonymous-initialisation',
        'INIT_ERROR_EVENT': 'wripl-anonymous-initialisation-error',
        'INIT_COMPLETE': 'wripl-anonymous-initialisation-complete',
        'LOAD_COMPLETE': 'wripl-anonymous-load-complete',
        'START_SPINNING_LOGO': 'wripl-start-spinning-logo'
    };

    jQuery(document).ready(function () {

        var template, compiledHtml, recommendations, recommendationsWithImage = [], recommendationsWithNoImage = [], sortedRecommendations = [];

        // Add listeners
        $("body").bind(events.INIT_EVENT, function (e, params) {
            console.log("Anonymous Widget: wripl-anonymous-initialisation heard");
        });

        $("body").bind(events.INIT_COMPLETE, function (e, params) {
            console.log("Widget: " + events.INIT_COMPLETE + " heard");
            $('#wripl-widget-ajax-container').html('wripl-anonymous-initialisation-complete so call /anonymous/recommendations and display them here!');
        });

        $("body").bind(events.LOAD_COMPLETE, function (e, params) {
            console.log("Widget: " +events.LOAD_COMPLETE +" heard");

            recommendations = params.recommendations;

            if (WriplWidgetProperties.widgetFormat === "withImages") {

                for (var i = 0; i < recommendations.length; i++) {
//                    recommendations[i].image = false;
                    if (recommendations[i].image) {

                        recommendationsWithImage.unshift(i);                            // remembering the index of each rec WITH an image
                        recommendations[i].imageSrc = recommendations[i].image[0];      // Adding a new property called 'imageSrc' to each recommendation which has an image
                        recommendations[i].imageHeight = WriplWidgetProperties.imageHeight;

                    } else {
                        recommendationsWithNoImage.unshift(i);                          // remembering the index of each rec WITHOUT an image
                    }
                }

                for (var k = 0; k < recommendationsWithImage.length; k++) {
                    sortedRecommendations.unshift(recommendations[recommendationsWithImage[k]]);
                }

                if (WriplWidgetProperties.handleRecommendationsWithoutImages === "append") {
                    recommendationsWithNoImage.reverse();                               // reverse the order.. to maintain integrity as push(ing) will naturally reverse.
                    for (var l = 0; l < recommendationsWithNoImage.length; l++) {
                        sortedRecommendations.push(recommendations[recommendationsWithNoImage[l]]);
                    }
                }

            } else {
                sortedRecommendations = recommendations;
            }

            if (WriplWidgetProperties.maxRecommendations) {
                sortedRecommendations = sortedRecommendations.slice(0, WriplWidgetProperties.maxRecommendations);
            }
            console.log("Widget: recommendation sliced based on maxRecommendations - fetching template widget/recommendations-active.html");

            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/widget/recommendations-active.html?ver=' + WriplAjaxProperties.pluginVersion, function (data) {

                console.log("Widget: template widget/recommendations-active.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplWidgetProperties: WriplWidgetProperties,
                    wriplAjaxProperties: WriplAjaxProperties,
                    recommendations: sortedRecommendations
                });

                $('#wripl-widget-ajax-container').html(compiledHtml);

                $('#wripl-widget-ajax-container .nailthumb-container').nailthumb();
                console.log("Widget: .nailthumb() called");

            });
        });

        $("body").bind(events.INIT_ERROR_EVENT, function (e) {
            console.log("Anonymous Widget: wripl-anonymous-initialisation-error heard");

            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/widget/recommendations-error.html?ver=' + WriplProperties.pluginVersion, function (data) {
                template = Handlebars.compile(data);
                compiledHtml = template();

                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });

        $("body").bind(events.START_SPINNING_LOGO, function (e) {
            console.log("Anonymous Widget: wripl-start-spinning-logo heard");

            var htmlOfSpinningLogo = "<img class='wripl-rotate' src='" + WriplProperties.pluginPath + "images/wripl-logo-rotate-orng-sml.png' >";
            $('#wripl-widget-ajax-container').html(htmlOfSpinningLogo);
        });
    });

})(jQuery, Handlebars);