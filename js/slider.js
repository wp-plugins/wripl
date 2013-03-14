(function ($, Handlebars) {

    jQuery(document).ready(function ($) {

        var sliderRevealed;
        var defaultPosition = -320;
        var displayAtPercent = 10;


        var slider = $("<div id='wripl-slider'></div>")
            .css({
                position:'fixed',
                bottom:20,
                //left:$(this).width() - 50 + 'px'
                right:defaultPosition
            });

        $('body').append(slider);

        // Add listeners
        $("body").bind("wripl-ajax-init-not-logged-in", function (e, params) {
            console.log("Not logged in!");
            console.log(e);
            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/slider/inactive.html', function (data) {

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplWidgetProperties:WriplWidgetProperties,
                    wriplAjaxProperties:WriplAjaxProperties
                });

                $('#wripl-slider').html(compiledHtml);

            });
        });

        $("body").bind("wripl-ajax-init-logged-in", function (e, params) {
            console.log("Logged in!");
            console.log(params);

            var firstImageUrl;

            theRecommendation = params.recommendations[0];

            if (theRecommendation.image){
                firstImageUrl = theRecommendation.image[0];
            }  else {
                firstImageUrl = "wripl-logo-sml.png";
            }
            console.log("firstImageUrl: "+firstImageUrl);

            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/slider/active.html', function (data) {
                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplAjaxProperties:WriplAjaxProperties,
                    post_title:theRecommendation.post_title,
                    permalink:theRecommendation.permalink,
                    featuredImage:firstImageUrl

                });

                $('#wripl-slider').html(compiledHtml);
            });
        });

        $('#wripl-slider').bind("mouseover", function(e) {
            console.log("If i want to do something on rollover");
        });

        $(document).scroll(function () {

            var scrollAmount = $(window).scrollTop();
            var documentHeight = $(document).height();
            var scrollPercent = (scrollAmount / documentHeight) * 100;

            var showSlider = function () {
                slider.animate(
                    {
                        right:20
                    }
                );
            }

            var hideSlider = function () {
                slider.animate(
                    {
                        right:defaultPosition
                    }
                );
            }

            if (scrollPercent > displayAtPercent && !sliderRevealed) {
                showSlider();
                sliderRevealed = true;
            }

            if (scrollPercent < displayAtPercent && sliderRevealed) {
                hideSlider();
                sliderRevealed = false;
            }

        });
    });
})(jQuery, Handlebars);