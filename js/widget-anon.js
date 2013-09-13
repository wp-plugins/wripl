console.log('widget-anon.js');
(function ($, Handlebars) {

    var events = {
        'INIT_ERROR_EVENT': 'wripl-anonymous-initialisation-error',
        'INIT_COMPLETE': 'wripl-anonymous-initialisation-complete',
        'START_SPINNING_LOGO': 'wripl-start-spinning-logo'
    };

    jQuery(document).ready(function () {

        var template, templateName, compiledHtml, recommendations, recommendationsWithImage = [], recommendationsWithNoImage = [], sortedRecommendations = [];

        // Add  the listeners
        $("body").bind(events.INIT_COMPLETE, function (e, response) {
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

            if(!templateName){
                console.log("Wripl anonymous initialisation error! There is no templateName set. Please contact your local wripl administrator.");
                $("body").trigger(events.INIT_ERROR_EVENT);
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

        $("body").bind(events.INIT_ERROR_EVENT, function (e) {
            console.log("Anonymous Widget: " + e.type + " heard");

            $.get(WriplProperties.pluginPath + 'handlebar-templates/widget/recommendations-error.html?ver=' + WriplProperties.pluginVersion, function (data) {
                template = Handlebars.compile(data);
                compiledHtml = template();
                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });

        $("body").bind(events.START_SPINNING_LOGO, function (e) {
            console.log("Anonymous Widget: " + e.type + " heard");

            var htmlOfSpinningLogo = "<img class='wripl-rotate' src='" + WriplProperties.pluginPath + "images/wripl-logo-rotate-orng-sml.png' >";
            $('#wripl-widget-ajax-container').html(htmlOfSpinningLogo);
        });
    });

})(jQuery, Handlebars);