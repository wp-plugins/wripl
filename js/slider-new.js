console.log('slider-new.js');
(function ($, Handlebars) {

    jQuery(document).ready(function ($) {

        var TEMPLATES_BASE_PATH = "handlebar-templates/";
        var TEMPLATES_MOBILE_SLIDER_PATH = "slider-mobile/";
        var TEMPLATES_SLIDER_PATH = "slider/";

        var templatesPath, sliderMode;
        var defaultPosition, activePosition, displayAtPercent;
        var thumbnailWidth, thumbnailHeight;

        var isMobile = (function () {
            var isMobileWide = (window.innerWidth < 768) ? true : false;
            var isMobileHigh = (window.innerHeight < 700) ? true : false;

            return isMobileWide || isMobileHigh;
        }());

        /**
         * Setup slider and add slider properties and methods for showing and hiding of component
         */

        var slider = $("<div id='wripl-slider'></div>");

        slider.forcedDisplayed = false;

        slider.show = function () {
            if (!this.displayed) {
                this.animate(
                    {
                        right:activePosition
                    }
                );
            }

            this.displayed = true;
            this.removeClass('show-left-pointer');
        };

        slider.hide = function () {
            if (this.displayed) {

                // Calculate the appropriate place to put the slider based on the width of the window at the time
                // For Landscape mode etc..
                (isMobile) ? adjustRight = $(window).width() * -1 : adjustRight = 0;

                this.animate(
                    {
                        right:adjustRight + defaultPosition
                    }
                );
            }

            this.displayed = false;
            this.addClass('show-left-pointer');
        };


        /**
         * Conditional stuff for the slider
         *
         * - initial html (div + conditional class for mobile) and css.
         * - display variables set
         * - nailthumb sizes
         * - template files location
         *
         * *NOTE* some visual config exists in style.css in #wripl-slider and #wripl-slider.wripl-mobile rules.
         */

        if (isMobile) {
            sliderMode = "Slider-Mobile";
            defaultPosition = 40;
            activePosition = 0;

            displayAtPercent = 50;

            thumbnailWidth = 80;
            thumbnailHeight = 61;

            templatesPath = WriplAjaxProperties.pluginPath + TEMPLATES_BASE_PATH + TEMPLATES_MOBILE_SLIDER_PATH;

            slider.addClass('wripl-mobile');
            slider.css({
                right:($(window).width() * -1) + defaultPosition
            });

        } else {
            sliderMode = "Slider";
            defaultPosition = -340;     // If the width of the slider changes.. so must this
            activePosition = 20;

            displayAtPercent = 70;

            thumbnailWidth = 132;
            thumbnailHeight = 100;

            templatesPath = WriplAjaxProperties.pluginPath + TEMPLATES_BASE_PATH + TEMPLATES_SLIDER_PATH;

            slider.css({
                right:defaultPosition
            });
        }


        /**
         * Event Listeners below.
         */

        $("body").bind("wripl-ajax-init-not-logged-in", function (e, params) {

            console.log(sliderMode + ": wripl-ajax-init-not-logged-in heard");
            console.log(sliderMode + ": not logged in - fetching template inactive.html");

            $.get(templatesPath + 'inactive.html', function (data) {

                console.log(sliderMode + ": inactive.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplAjaxProperties:WriplAjaxProperties
                });

                $('#wripl-slider').html(compiledHtml);

            });
        });

        $("body").bind("wripl-ajax-init-logged-in", function (e, params) {

            console.log(sliderMode + ": wripl-ajax-init-logged-in heard");
            var thumbnailPath;

            // If there are no recommendations
            if (params.recommendations.length === 0) {
                $.get(templatesPath + 'no-recommendations.html', function (data) {

                    console.log(sliderMode + ": no-recommendations.html fetched");
                    template = Handlebars.compile(data);
                    compiledHtml = template({
                        wriplAjaxProperties:WriplAjaxProperties
                    });
                    $('#wripl-slider').html(compiledHtml);
                });

                //returning early
                return;
            }

            params.recommendations = truncateTitles(params.recommendations);

            var theRecommendation = params.recommendations[0];      // set theRecommendation to be the FIRST

            if (theRecommendation.image) {
                thumbnailPath = theRecommendation.image[0];
                //theRecommendation.image = false;                  // uncomment to simulate no 'feature' image
            } else {
                thumbnailPath = WriplAjaxProperties.pluginPath + "/images/wripl-logo-sml.png";
            }

            console.log(sliderMode + ": recommendation stripped - fetching template active.html");

            $.get(templatesPath + 'active.html', function (data) {

                console.log(sliderMode + ": active.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplAjaxProperties:WriplAjaxProperties,
                    post_title:theRecommendation.post_title,
                    permalink:theRecommendation.permalink,
                    thumbnail:thumbnailPath
                });

                $('#wripl-slider').html(compiledHtml);

                $('#wripl-slider .wripl-thumbnail').nailthumb(
                    {
                        width:thumbnailWidth,
                        height:thumbnailHeight
                    }
                );

                if (!theRecommendation.image) {
                    console.log(sliderMode + ": No feature image set");
                    $('.wripl-thumbnail').remove();
                }

            });
        });

        $("body").bind("wripl-ajax-init-error", function (e, params) {
            console.log(sliderMode + ": wripl-ajax-init-error heard");
            $('#wripl-slider').remove();
        });


        /**
         * Watching for page scrolling
         */

        $(document).scroll(function () {

            var scrollAmount = $(window).scrollTop();
            var documentHeight = $(document).height();
            var scrollPercent = (scrollAmount / documentHeight) * 100;

            if (!slider.forcedDisplayed) {
                if (scrollPercent > displayAtPercent) {
                    slider.show();
                }

                if (scrollPercent < displayAtPercent) {
                    slider.hide();
                }
            }

        });




        /**
         * Add the slider to the page
         */

        $('body').append(slider);


        /**
         * Click Listeners below.
         */

        $('#wripl-slider').click(function (event) {

                if (!slider.displayed) {
                    event.preventDefault();
                    slider.show();
                    slider.forcedDisplayed = true;
                }
            }
        );

        $('#wripl-slider').on('click', 'a.dismiss', function (event) {
            event.stopPropagation();
            slider.hide();
            slider.forcedDisplayed = true;

        });


    });


    /**
     * Some Helpers
     */

    String.prototype.trunc = function (n) {
        return this.substr(0, n - 1) + (this.length > n ? '&hellip;' : '');
    };

    var truncateTitles = function (theArray) {
        for (var i = 0; i < theArray.length; i++) {
            theArray[i].post_title = theArray[i].post_title.trunc(47);
        }
        return theArray;
    };

})(jQuery, Handlebars);
