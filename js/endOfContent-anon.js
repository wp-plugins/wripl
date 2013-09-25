console.log('endOfContent-anon.js');
(function ($, Handlebars) {

    jQuery(document).ready(function () {

        var template, templateName, compiledHtml, recommendations,
            recommendationsWithImage = [],
            recommendationsWithNoImage = [],
            sortedRecommendations = [],
            thumbNailSize = 120;

        // Add  the listeners
        $("body").bind(WriplEvents.INIT_COMPLETE, function (e, response) {
            console.log("Anonymous end-of-content: " + e.type + " heard");

            recommendations = response.recommendations;

            templateName = "recommendations";

            for (var i = 0; i < recommendations.length; i++) {
                if (recommendations[i].hasOwnProperty('imageUrl')) {
                    recommendationsWithImage.unshift(i);
                } else {
                    recommendationsWithNoImage.unshift(i);                                  // remembering the index of each rec WITHOUT an image
                }
            }

            for (var k = 0; k < recommendationsWithImage.length; k++) {
                sortedRecommendations.unshift(recommendations[recommendationsWithImage[k]]);
            }

            if(!templateName){
                console.log("Wripl Anonymous end-of-content: initialisation error! There is no templateName set. Please contact your local wripl administrator.");
                $("body").trigger(WriplEvents.INIT_ERROR);
                return;
            }

            if (recommendationsWithImage.length === 0) {
                console.log("Wripl Anonymous end-of-content: No feature images in any reccomendations - removing the #wripl-end-of-content element");
                $('#wripl-end-of-content').remove();
                return;
            }

            $.get(WriplProperties.pluginPath + 'handlebar-templates/anonymous-endOfContent/'+ templateName +'.html?ver=' + WriplProperties.pluginVersion, function (data) {
                console.log("Anonymous end-of-content: template "+ templateName+".html fetched.");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplProperties: WriplProperties,
                    recommendations: sortedRecommendations
                });

                $('#wripl-end-of-content').html(compiledHtml);
                $('#wripl-end-of-content .nailthumb-eoc-container').nailthumb(
                    {
                        width: thumbNailSize,
                        height: thumbNailSize,
                        imageClass: 'nailthumb-eoc-image',
                        containerClass:'nailthumb-eoc-container'
                    }
                );
                console.log("Anonymous end-of-content: .nailthumb() called");

            });
        });

        $("body").bind(WriplEvents.INIT_ERROR, function (e) {
            console.log("Anonymous end-of-content: " + e.type + " heard");
            alert('hide the endofcontent stuff due to an error');

        });

        $("body").bind(WriplEvents.INIT_START, function (e) {
            console.log("Anonymous end-of-content: " + e.type + " heard");

            // Spin the logo
            var htmlOfSpinningLogo = "<img class='wripl-rotate' src='" + WriplProperties.pluginPath + "images/wripl-logo-rotate-orng-sml.png' >";
            $('#wripl-end-of-content').html(htmlOfSpinningLogo);
        });
    });

})(jQuery, Handlebars);