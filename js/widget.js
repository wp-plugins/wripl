console.log('widget.js');
(function ($, Handlebars) {

    jQuery(document).ready(function () {

        var template, compiledHtml, recommendationsArray;

        // Add listeners
        $("body").bind( "wripl-ajax-init-not-logged-in" , function (e, params) {
            console.log("Widget: wripl-ajax-init-not-logged-in heard");

            console.log("Widget: not logged in - fetching template widget/recommendations-inactive.html");
            $.get( WriplAjaxProperties.pluginPath + 'handlebar-templates/widget/recommendations-inactive.html?ver=' + WriplAjaxProperties.pluginVersion, function(data) {

                console.log("Widget: template widget/recommendations-inactive.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplWidgetProperties: WriplWidgetProperties,
                    wriplAjaxProperties: WriplAjaxProperties
                });

                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });

        $("body").bind( "wripl-ajax-init-logged-in" , function (e, params) {
            console.log("Widget: wripl-ajax-init-logged-in heard");

            recommendationsArray = params.recommendations;

            if( WriplWidgetProperties.maxRecommendations ){
                recommendationsArray = recommendationsArray.slice(0 , WriplWidgetProperties.maxRecommendations);
            }

            console.log("Widget: recommendation sliced - fetching template widget/recommendations-active.html");

            $.get( WriplAjaxProperties.pluginPath + 'handlebar-templates/widget/recommendations-active.html?ver=' + WriplAjaxProperties.pluginVersion, function(data) {

                console.log("Widget: template widget/recommendations-active.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplWidgetProperties: WriplWidgetProperties,
                    wriplAjaxProperties: WriplAjaxProperties,
                    recommendations: recommendationsArray
                });

                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });

        $("body").bind( "wripl-ajax-init-error" , function (e) {
            console.log("some error!");

            $.get( WriplAjaxProperties.pluginPath + 'handlebar-templates/widget/recommendations-error.html?ver=' + WriplAjaxProperties.pluginVersion, function(data) {
                template = Handlebars.compile(data);
                compiledHtml = template();

                $('#wripl-widget-ajax-container').html(compiledHtml);
            });
        });

    });

})(jQuery,Handlebars);