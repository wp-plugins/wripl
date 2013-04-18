console.log('widget.js');
(function ($, Handlebars) {

    jQuery(document).ready(function () {

        var template, compiledHtml, recommendations, recommendationsWithNoImage = [];

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
                    if (recommendations[i].image) {
                        // Adding a new property called 'imageSrc' to each recommendation which has an image
                        recommendations[i].imageSrc = recommendations[i].image[0];
                    } else {
                        // remember the indexes of recommendations with no feature image
                        recommendationsWithNoImage.push(i);
                    }
                }
            }

            // removes recommendations without images and conditionally.. moves them to the end of the recommendations array
            for (var j = recommendationsWithNoImage.length-1; j >= 0; j--) {
                var toBeMoved = recommendations[recommendationsWithNoImage[j]];
                recommendations.splice(recommendationsWithNoImage[j], 1);
                if (WriplWidgetProperties.handleRecommendationsWithoutImages === "append") {
                    recommendations.push(toBeMoved);
                }
            }

            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/widget/recommendations-active.html?ver=' + WriplAjaxProperties.pluginVersion, function (data) {

                console.log("Widget: template widget/recommendations-active.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplWidgetProperties: WriplWidgetProperties,
                    wriplAjaxProperties: WriplAjaxProperties,
                    recommendations: recommendations
                });

                $('#wripl-widget-ajax-container').html(compiledHtml);

                $('#wripl-widget-ajax-container .wripl-widget-thumbnail').nailthumb(
                    {
//                      good sizes:
//                        height:161,
//                        width:100,
//                        width:113,
//                        height:70
//                        ... or no width at all
                        height: WriplWidgetProperties.imageHeight

                    }
                );
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