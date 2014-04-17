console.log('slider-anon.js');
(function ($, Handlebars) {

    jQuery(document).ready(function ($) {

        var TEMPLATES_BASE_PATH = "handlebar-templates/",
            TEMPLATES_MOBILE_SLIDER_PATH = "anonymous-slider-mobile/",
            TEMPLATES_SLIDER_PATH = "anonymous-slider/",
            recommendations,
            templatesPath,
            sliderMode,
            defaultPosition,
            activePosition,
            displayAtPercent,
            thumbnailWidth,
            thumbnailHeight,
            isMobile = (function () {
                var isMobileWide = (window.innerWidth < 768) ? true : false;
                var isMobileHigh = (window.innerHeight < 500) ? true : false;
                return isMobileWide || isMobileHigh;
            }());

        /**
         * Setup slider and add slider properties and methods for showing and hiding of component
         */

        var slider = $("<div id='wripl-slider-container' class='wripl-ajax-container'></div>");
        slider.forcedDisplayed = false;

        slider.show = function (isMobile) {
            if (isMobile) {
                if (!this.displayed) {
                    this.addClass("wripl-mobile-slider-active");
                    this.displayed = true;
                    this.removeClass('show-left-pointer');
                }
            } else {
                if (!this.displayed) {
                    this.animate(
                        {
                            right: activePosition
                        }
                    );
                    this.displayed = true;
                    this.removeClass('show-left-pointer');
                }
            }
        };

        slider.hide = function (isMobile) {
            if (isMobile) {
                if (this.displayed) {
                    this.removeClass("wripl-mobile-slider-active");
                }

            } else {
                if (this.displayed) {

                    // Calculate the appropriate place to put the slider based on the width of the window at the time
                    // For Landscape mode etc.. (non-mobile)
                    (isMobile) ? adjustRight = $(window).width() * -1 : adjustRight = 0;

                    this.animate({right:adjustRight + defaultPosition});
                }
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
         * *NOTE* some visual config exists in style.css in #wripl-slider-container and #wripl-slider-container.wripl-mobile rules.
         */

        if (isMobile) {
            sliderMode = "Slider-Mobile";
            defaultPosition = -80;
            activePosition = 0;         // activePosition for the mobile slider is flush to the bottom of the screen

            thumbnailWidth = 110;
            thumbnailHeight = 68;

            templatesPath = WriplProperties.pluginPath + TEMPLATES_BASE_PATH + TEMPLATES_MOBILE_SLIDER_PATH;

            slider.addClass('wripl-mobile');

        } else {
            sliderMode = "Slider";
            defaultPosition = -340;     // If the desired width of the slider changes.. so must this
            activePosition = 20;

            displayAtPercent = 65;

            thumbnailWidth = 132;
            thumbnailHeight = 100;

            templatesPath = WriplProperties.pluginPath + TEMPLATES_BASE_PATH + TEMPLATES_SLIDER_PATH;

            slider.css({
                right:defaultPosition
            });
        }

        /**
         * Event Listeners below.
         */

        $("body").bind(WriplRecommendationEvents.INIT_COMPLETE, function (e, response) {

            console.log("Anonymous Widget: " + sliderMode + ": " + e.type + " heard");

            var imageSrc;
            recommendations = response.recommendations;
            recommendations = truncateTitles(recommendations);

            var theRecommendation = recommendations[0];      // set theRecommendation to be the FIRST

            if (theRecommendation.hasOwnProperty('imageUrl')) {
                imageSrc = theRecommendation.imageUrl;
            } else {
                imageSrc = WriplProperties.pluginPath + "/images/wripl-logo-sml.png";
            }

            console.log(sliderMode + ": first recommendation set - fetching template 'recommendations.html'");

            $.get(templatesPath + 'recommendations.html?ver=' + WriplProperties.pluginVersion, function (data) {

                console.log(sliderMode + ": recommendations.html fetched");

                template = Handlebars.compile(data);
                compiledHtml = template({
                    wriplProperties:WriplProperties,
                    title: theRecommendation.title,
                    linkUrl: theRecommendation.linkUrl,
                    imageSrc: imageSrc
                });

                $('#wripl-slider-container').html(compiledHtml);

                $('#wripl-slider-container .wripl-thumbnail').nailthumb(
                    {
                        width: thumbnailWidth,
                        height: thumbnailHeight
                    }
                );

                if (imageSrc === "") {
                    console.log(sliderMode + ": No feature image set");
                    $('.wripl-thumbnail').remove();
                }

                // hide the slider by default
                slider.displayed = true;
                slider.hide(isMobile);

            });
        });

        // If there is an error with wripl.. remove the slider
        $("body").bind(WriplRecommendationEvents.INIT_ERROR, function (e, params) {
            console.log(sliderMode + " " + e.type + " : heard");
            $('#wripl-slider-container').remove();
        });

        var lastScrollTop = 0;

        /**
         * Watching for page scrolling
         */
        $(document).scroll(function () {

            var scrollPercent = (
                $(window).scrollTop() / ($(document).height() - $(window).height())
            ) * 100;

            if (!slider.forcedDisplayed) {
                if (scrollPercent > displayAtPercent) {
                    slider.show(isMobile);
                }

                if (scrollPercent < displayAtPercent) {
                    slider.hide(isMobile);
                }
            }

            if (isMobile) {

                var st = $(this).scrollTop();

                if (st > lastScrollTop) {
                    // downscroll code
                    slider.hide(isMobile);
                } else {
                    // upscroll code
                    slider.show(isMobile);
                }
                lastScrollTop = st;
            }

        });

        /**
         * If page is too small to scroll,
         * force the slider out.
         */
        if ($(window).height() >= $(document).height()) {
            slider.forcedDisplayed = true;
            slider.show(isMobile);
        }

        /**
         * Add the slider to the page
         */
        $('body').append(slider);

        /**
         * Click Listeners below.
         */
        $('#wripl-slider-container').click(function (event) {
            if (!slider.displayed) {
                event.preventDefault();
                slider.show(isMobile);
                slider.forcedDisplayed = true;
            }
        });

        $('#wripl-slider-container').on('click', 'a.dismiss', function (event) {

            event.stopPropagation();
            slider.hide(isMobile);
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
            theArray[i].title = theArray[i].title.trunc(47);
        }
        return theArray;
    };

})(jQuery, Handlebars);
