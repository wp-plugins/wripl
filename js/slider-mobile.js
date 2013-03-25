//alert('hello mr mobile fancy pants');

console.log('slider-mobile.js');
(function ($, Handlebars) {

    jQuery(document).ready(function ($) {

        var defaultPosition = -100;
        var displayAtPercent = 10;

        console.log(defaultPosition);

        var slider = $("<div id='wripl-slider' class='wripl-mobile'></div>")
            .css({
                position: 'fixed',
                bottom: 20,
                //left:$(this).width() - 50 + 'px'
                right: defaultPosition,
                "z-index": 100
            });

        slider.forcedDisplayed = false;

        slider.show = function () {
            if(!this.displayed)
            {
                this.animate(
                    {
                        right: 20
                    }
                );
            }

            this.displayed = true;
        }

        slider.hide = function () {
            if(this.displayed)
            {
                this.animate(
                    {
                        right: defaultPosition
                    }
                );
            }

            this.displayed = false;
        }

        $('body').append(slider);

        $('#wripl-slider').click(function (event) {
                event.stopPropagation()
                slider.show();
                slider.forcedDisplayed = true;
            }
        );

        /**
         * Watching for page scrolling
         */
        $(document).scroll(function () {

            var scrollAmount = $(window).scrollTop();
            var documentHeight = $(document).height();
            var scrollPercent = (scrollAmount / documentHeight) * 100;

            if(!slider.forcedDisplayed)
            {
                if (scrollPercent > displayAtPercent) {
                    slider.show();
                }

                if (scrollPercent < displayAtPercent) {
                    slider.hide();
                }
            }

        });


        /**
         * Listeners below.
         */

        $("body").bind("wripl-ajax-init-not-logged-in", function (e, params) {
            console.log("Slider: wripl-ajax-init-not-logged-in heard");

            console.log("Slider: not logged in - fetching template inactive.html");

            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/slider-mobile/inactive.html', function (data) {

                console.log("Slider: template slider/inactive.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplAjaxProperties: WriplAjaxProperties
                });

                $('#wripl-slider').html(compiledHtml);

            });
        });

        $("body").bind("wripl-ajax-init-logged-in", function (e, params) {
            console.log("Slider: wripl-ajax-init-logged-in heard");

            var thumbnailPath;

            // If there are no recommendations
            if (params.recommendations.length == 0) {
                console.log("Slider-Mobile: no recommendations - fetching template no-recommendations.html");

//                $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/slider/no-recommendations.html', function (data) {
//
//                    console.log("Slider: template slider/no-recommendations.html fetched");
//
//                    template = Handlebars.compile(data);
//                    compiledHtml = template({
//                        wriplAjaxProperties:WriplAjaxProperties
//                    });
//                    $('#wripl-slider').html(compiledHtml);
//                });

                //returning early
                return
            }

            params.recommendations = truncateTitles(params.recommendations);

            var theRecommendation = params.recommendations[0];      // set theRecommendation to be the FIRST

            if (theRecommendation.image) {
                thumbnailPath = theRecommendation.image[0];
                //theRecommendation.image = false;                  // uncomment to simulate no 'feature' image
            } else {
                thumbnailPath = WriplAjaxProperties.pluginPath + "/images/wripl-logo-sml.png";
            }

            console.log("Slider: recommendation stripped - fetching template active.html");
            $.get(WriplAjaxProperties.pluginPath + 'handlebar-templates/slider-mobile/active.html', function (data) {

                console.log("Slider: template slider/active.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplAjaxProperties: WriplAjaxProperties,
                    post_title: theRecommendation.post_title,
                    permalink: theRecommendation.permalink,
                    thumbnail: thumbnailPath
                });

                $('#wripl-slider').html(compiledHtml);

                $('#wripl-slider .wripl-thumbnail').nailthumb(
                    {
                        width: 132,
                        height: 100
                        //method: 'resize'
                    }
                );

                if (!theRecommendation.image) {
                    console.log("Slider: No feature image set");
                    $('.wripl-thumbnail').remove();
                }

            });
        });

        $("body").bind("wripl-ajax-init-error", function (e, params) {
            console.log("Slider: wripl-ajax-init-error heard");

//            $('#wripl-slider').remove();

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