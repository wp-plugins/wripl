(function ($, Handlebars) {

    jQuery(document).ready(function ($) {

        var sliderRevealed;
        var defaultPosition = -370;
        var displayAtPercent = 10;

        var slider = $("<div id='wripl-slider'><img src='/slide-out-from-side-mockup-withdropshadow.png'></div>")
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



            theRecommendation = params.recommendations[1];
            if (theRecommendation.image) var firstImageUrl = theRecommendation.image[0];
            console.log("firstImageUrl: "+firstImageUrl);


            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/slider/active.html', function (data) {
                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplAjaxProperties:WriplAjaxProperties,
                    recommendation:theRecommendation,
                    featuredImage:"test.png"
                });

                $('#wripl-slider').html(compiledHtml);
            });
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