console.log('widget-anon.js');
(function ($, Handlebars) {

    jQuery(document).ready(function () {

        var template, templateName, compiledHtml, recommendations, recommendationsWithImage = [], recommendationsWithNoImage = [], sortedRecommendations = [];

        // Add  the listeners
        $("body").bind(WriplEvents.INIT_COMPLETE, function (e, response) {
            console.log("Anonymous Widget: " + e.type + " heard");

            recommendations = response.recommendations;

            if (WriplWidgetProperties.widgetFormat === "withImages") {
                templateName = "recommendations";

                for (var i = 0; i < recommendations.length; i++) {

                    if (recommendations[i].imageUrl) {
                        recommendationsWithImage.unshift(i);                                    // remembering the index of each rec WITH an image
                        recommendations[i].imageHeight = WriplWidgetProperties.imageHeight;     // adding an 'imageHeight' property to each recommendation to get around the scope issue within
                                                                                                // handlebars template iterators. This is a hacky fix. Todo: Review this hack... There's probably a handlebars helper to sort this out.
                    } else {
                        recommendationsWithNoImage.unshift(i);                                  // remembering the index of each rec WITHOUT an image
                    }
                }

                for (var k = 0; k < recommendationsWithImage.length; k++) {
                    sortedRecommendations.unshift(recommendations[recommendationsWithImage[k]]);
                }

                if (WriplWidgetProperties.handleRecommendationsWithoutImages === "append") {
                    recommendationsWithNoImage.reverse();                                       // reverse the order.. to maintain integrity as push(ing) will naturally reverse.
                    for (var l = 0; l < recommendationsWithNoImage.length; l++) {
                        sortedRecommendations.push(recommendations[recommendationsWithNoImage[l]]);
                    }
                }

            } else {
                templateName = "textOnlyRecommendations";
                sortedRecommendations = recommendations;
            }

            if (WriplWidgetProperties.maxRecommendations) {
                sortedRecommendations = sortedRecommendations.slice(0, WriplWidgetProperties.maxRecommendations);
            }

            console.log("Widget: recommendation sliced based on maxRecommendations");

            if(!templateName){
                console.log("Wripl anonymous initialisation error! There is no templateName set. Please contact your local wripl administrator.");
                $("body").trigger(WriplEvents.INIT_ERROR);
                return;
            }

            $.get(WriplProperties.pluginPath + 'handlebar-templates/anonymous-widget/'+ templateName +'.html?ver=' + WriplProperties.pluginVersion, function (data) {
                console.log("Anonymous Widget: template "+ templateName+".html fetched.");

                template = Handlebars.compile(data);

                compiledHtml = template({
                    wriplWidgetProperties: WriplWidgetProperties,
                    wriplProperties: WriplProperties,
                    recommendations: sortedRecommendations
                });

                $('#wripl-widget-ajax-container').html(compiledHtml);
                $('#wripl-widget-ajax-container .nailthumb-container').nailthumb();
                console.log("Anonymous Widget: .nailthumb() called");

            });
        });

        $("body").bind(WriplEvents.INIT_ERROR, function (e) {
            console.log("Anonymous Widget: " + e.type + " heard");

            $.get(WriplProperties.pluginPath + 'handlebar-templates/widget/recommendations-error.html?ver=' + WriplProperties.pluginVersion, function (data) {
                template = Handlebars.compile(data);
                compiledHtml = template();
                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });

        $("body").bind(WriplEvents.INIT_START, function (e) {
            console.log("Anonymous Widget: " + e.type + " heard");

            // Spin the logo
            var htmlOfSpinningLogo = "<img class='wripl-rotate' src='" + WriplProperties.pluginPath + "images/wripl-logo-rotate-orng-sml.png' >";
            $('#wripl-widget-ajax-container').html(htmlOfSpinningLogo);
        });
    });

})(jQuery, Handlebars);