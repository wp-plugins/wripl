console.log('slider.js');
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
            console.log("Slider: wripl-ajax-init-not-logged-in heard");

            console.log("Slider: not logged in - fetching template inactive.html");
            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/slider/inactive.html', function (data) {

                console.log("Slider: template slider/inactive.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplAjaxProperties:WriplAjaxProperties
                });

                $('#wripl-slider').html(compiledHtml);

            });
        });


        $("body").bind("wripl-ajax-init-logged-in", function (e, params) {
            console.log("Slider: wripl-ajax-init-logged-in heard");

            var firstImageUrl;

            // If there are no recommendations!
            if (params.recommendations.length == 0) {
                console.log("Slider: no recommendations - fetching template no-recommendations.html");
                $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/slider/no-recommendations.html', function (data) {

                    console.log("Slider: template slider/no-recommendations.html fetched");

                    template = Handlebars.compile(data);
                    compiledHtml = template({
                        wriplAjaxProperties:WriplAjaxProperties
                    });
                    $('#wripl-slider').html(compiledHtml);
                });

                //returning early
                return
            }

            params.recommendations = truncateTitles(params.recommendations);
            theRecommendation = params.recommendations[0];

            if (theRecommendation.image) {
                firstImageUrl = theRecommendation.image[0];
            } else {
                firstImageUrl = WriplAjaxProperties.pluginPath + "/images/wripl-logo-sml.png";          //show our logo if there is no image
            }


            console.log("Slider: recommendation stripped - fetching template active.html");
            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/slider/active.html', function (data) {

                console.log("Slider: template slider/active.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplAjaxProperties:WriplAjaxProperties,
                    post_title:theRecommendation.post_title,
                    permalink:theRecommendation.permalink,
                    featuredImage:firstImageUrl
                });

                $('#wripl-slider').html(compiledHtml);
                $('#wripl-slider .thumbnail').nailthumb(
                    {
                        width:132,
                        height:100,
                        //method: 'resize'
                    }
                );

            });
        });

        $("body").bind("wripl-ajax-init-error", function (e, params) {
            console.log("Slider: wripl-ajax-init-error heard");
            $('#wripl-slider').remove();

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

    String.prototype.trunc = function (n) {
        return this.substr(0, n - 1) + (this.length > n ? '&hellip;' : '');
    };

    var truncateTitles = function (theArray) {
        for (var i = 0; i < theArray.length; i++) {
            theArray[i].post_title = theArray[i].post_title.trunc(47);
        }
        return theArray;
    }


})(jQuery, Handlebars);