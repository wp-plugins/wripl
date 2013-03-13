(function ($, Handlebars) {

    jQuery(document).ready(function () {

        var template, compiledHtml, recommendationsArray;

        // Add listeners
        $("body").bind( "wripl-ajax-init-not-logged-in" , function (e, params) {
            console.log("Not logged in!");
            console.log(e);
            $.get( WriplAjaxProperties.pluginPath + 'handlebar-templates/widget/recommendations-inactive.html', function(data) {

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplWidgetProperties: WriplWidgetProperties,
                    wriplAjaxProperties: WriplAjaxProperties
                });

                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });

        $("body").bind( "wripl-ajax-init-logged-in" , function (e, params) {
            console.log("Logged in!");
            console.log(params);
            recommendationsArray = params.recommendations;

            if( WriplWidgetProperties.maxRecommendations ){
                recommendationsArray = recommendationsArray.slice(0 , WriplWidgetProperties.maxRecommendations);
            }

            $.get( WriplAjaxProperties.pluginPath + 'handlebar-templates/widget/recommendations-active.html', function(data) {
                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplWidgetProperties: WriplWidgetProperties,
                    wriplAjaxProperties: WriplAjaxProperties,
                    recommendations: recommendationsArray
                });

                console.log(compiledHtml);
                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });


    });

})(jQuery, Handlebars);