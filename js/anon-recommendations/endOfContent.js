(function ($, Handlebars) {

    jQuery(document).ready(function () {

        var template, templateName, compiledHtml,
            recommendationsWithImage = [],
            recommendationsWithNoImage = [],
            sortedRecommendations = [],
            thumbNailSize = 120;

        // Add  the listeners
        $("body").bind(WriplRecommendationEvents.INIT_COMPLETE, function (e, response) {
            console.log("Anonymous end-of-content: " + e.type + " heard");

            var recommendations = response.recommendations;
            templateName = "recommendations";

            for (var i = 0; i < recommendations.length; i++) {
                if (recommendations[i].hasOwnProperty('imageUrl')) {                            // IF the recommendation has a property called 'imageURL'
                    if (recommendations[i].imageUrl !== ""){                                    // AND IF the imageUrl is not empty
                        recommendationsWithImage.unshift(i);
                    } else {
                        recommendationsWithNoImage.unshift(i);                                  // remembering the index of each rec WITHOUT an image
                    }
                } else {
                    $("body").trigger(WriplRecommendationEvents.INIT_ERROR);
                }
            }

            for (var k = 0; k < recommendationsWithImage.length; k++) {
                sortedRecommendations.unshift(recommendations[recommendationsWithImage[k]]);
            }

            if(!templateName){
                console.log("Wripl Anonymous end-of-content: initialisation error! There is no 'templateName' set.");
                $("body").trigger(WriplRecommendationEvents.INIT_ERROR);
                return;
            }

            if (recommendationsWithImage.length < 1) {
                console.log("Wripl Anonymous end-of-content: No feature images in any recommendations - removing the #wripl-end-of-content-recommendations-container element");
                $('#wripl-end-of-content-recommendations-container').remove();
                return;
            }

            $.get(WriplProperties.pluginPath + 'handlebar-templates/anonymous-endOfContent/'+ templateName +'.html?ver=' + WriplProperties.pluginVersion, function (data) {
                console.log("Anonymous end-of-content: template "+ templateName+".html fetched.");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplProperties: WriplProperties,
                    recommendations: sortedRecommendations
                });

                $('#wripl-end-of-content-recommendations-container').html(compiledHtml);
                $('#wripl-end-of-content-recommendations-container .nailthumb-eoc-container').nailthumb(
                    {
                        width: thumbNailSize,
                        height: thumbNailSize,
                        imageClass: 'nailthumb-eoc-image',
                        containerClass:'nailthumb-eoc-container'
                    }
                );
                console.log("Anonymous end-of-content: .nailthumb() called");

                $("body").trigger(WriplRecommendationEvents.TEMPLATE_FETCHED);
            });
        });

        $("body").bind(WriplRecommendationEvents.INIT_ERROR, function (e) {
            console.log("Anonymous end-of-content: " + e.type + " heard");
            $('#wripl-end-of-content-recommendations-container').remove();

        });

        $("body").bind(WriplRecommendationEvents.INIT_START, function (e) {
            console.log("Anonymous end-of-content: " + e.type + " heard");

            // Spin the logo
            var htmlOfSpinningLogo = "<img class='wripl-rotate' src='" + WriplProperties.pluginPath + "images/wripl-logo-rotate-orng-sml.png' >";
            $('#wripl-end-of-content-recommendations-container').html(htmlOfSpinningLogo);
        });
    });

})(jQuery, Handlebars);