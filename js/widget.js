console.log('widget.js');
(function ($, Handlebars) {

    jQuery(document).ready(function () {

        var template, compiledHtml, recommendations, recommendationsWithImage = [], recommendationsWithNoImage = [], sortedRecommendations = [];

        // Add listeners
        $("body").bind("wripl-ajax-init-not-logged-in", function (e, params) {
            console.log("Widget: wripl-ajax-init-not-logged-in heard");

            console.log("Widget: not logged in - fetching template widget/recommendations-inactive.html");
            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/widget/recommendations-inactive.html?ver=' + WriplAjaxProperties.pluginVersion, function (data) {

                console.log("Widget: template widget/recommendations-inactive.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplWidgetProperties: WriplWidgetProperties,
                    wriplAjaxProperties: WriplAjaxProperties
                });

                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });

        $("body").bind("wripl-ajax-init-logged-in", function (e, params) {
            console.log("Widget: wripl-ajax-init-logged-in heard");

            recommendations = params.recommendations;

            if (WriplWidgetProperties.maxRecommendations) {
                recommendations = recommendations.slice(0, WriplWidgetProperties.maxRecommendations);
            }
            console.log("Widget: recommendation sliced - fetching template widget/recommendations-active.html");

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

        $("body").bind("wripl-ajax-init-error", function (e) {
//            console.log("some error!");
            console.log("Widget: wripl-ajax-init-error heard");

            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/widget/recommendations-error.html?ver=' + WriplAjaxProperties.pluginVersion, function (data) {
                template = Handlebars.compile(data);
                compiledHtml = template();

                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });

    });

})(jQuery, Handlebars);