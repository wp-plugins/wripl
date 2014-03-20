(function ($, Handlebars) {

    jQuery(document).ready(function () {

        var template, templateName, compiledHtml,
            mostEngagingWithImage = [],
            mostEngagingWithNoImage = [],
            sortedMostEngaging = [],
            thumbNailSize = 120;

        // Add the listeners
        $("body").bind(WriplMostEngagingEvents.INIT_COMPLETE, function (e, response) {
            var mostEngaging = response.mostEngaging;
            templateName = "most-engaging";

            for (var i = 0; i < mostEngaging.length; i++) {
                if (mostEngaging[i].hasOwnProperty('imageUrl')) {                            // IF the recommendation has a property called 'imageURL'
                    if (mostEngaging[i].imageUrl !== ""){                                    // AND IF the imageUrl is not empty
                        mostEngagingWithImage.unshift(i);
                    } else {
                        mostEngagingWithNoImage.unshift(i);                                  // remembering the index of each rec WITHOUT an image
                    }
                } else {
                    $("body").trigger(WriplMostEngagingEvents.INIT_ERROR);
                }
            }

            for (var k = 0; k < mostEngagingWithImage.length; k++) {
                sortedMostEngaging.unshift(mostEngaging[mostEngagingWithImage[k]]);
            }

            if(!templateName){
                console.log("Wripl Anonymous end-of-content: initialisation error! There is no 'templateName' set.");
                $("body").trigger(WriplMostEngagingEvents.INIT_ERROR);
                return;
            }

            if (mostEngagingWithImage.length < 1) {
                console.log("Wripl Anonymous end-of-content: No feature images in any recommendations - removing the #wripl-end-of-content-most_engaging-container element");
                $('#wripl-end-of-content-most_engaging-container').remove();
                return;
            }

            $.get(WriplProperties.pluginPath + 'handlebar-templates/anonymous-endOfContent/'+ templateName +'.html?ver=' + WriplProperties.pluginVersion, function (data) {
                console.log("Anonymous end-of-content: template "+ templateName+".html fetched.");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplProperties: WriplProperties,
                    mostEngaging: sortedMostEngaging
                });

                $('#wripl-end-of-content-most_engaging-container').html(compiledHtml);
                $('#wripl-end-of-content-most_engaging-container .nailthumb-eoc-container').nailthumb(
                    {
                        width: thumbNailSize,
                        height: thumbNailSize,
                        imageClass: 'nailthumb-eoc-image',
                        containerClass:'nailthumb-eoc-container'
                    }
                );
                console.log("Anonymous end-of-content: .nailthumb() called");

                $("body").trigger(WriplMostEngagingEvents.TEMPLATE_FETCHED);
            });
        });

        $("body").bind(WriplMostEngagingEvents.INIT_ERROR, function (e) {
            console.log("Anonymous end-of-content: " + e.type + " heard");
            $('#wripl-end-of-content-most_engaging-container').remove();

        });

        $("body").bind(WriplMostEngagingEvents.INIT_START, function (e) {
            console.log("Anonymous end-of-content: " + e.type + " heard");

            // Spin the logo
            var htmlOfSpinningLogo = "<img class='wripl-rotate' src='" + WriplProperties.pluginPath + "images/wripl-logo-rotate-orng-sml.png' >";
            $('#wripl-end-of-content-most_engaging-container').html(htmlOfSpinningLogo);
        });
    });

})(jQuery, Handlebars);