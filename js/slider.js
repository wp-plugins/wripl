jQuery(document).ready(function ($) {

    var sliderRevealed;
    var defaultPosition = -370;
    var displayAtPercent = 10;

    var slider = $("<div id='wripl-slider'><img src='/slide-out-from-side-mockup-withdropshadow.png'></div>")
        .css({
            position:'fixed',
            bottom:0,
            //left:$(this).width() - 50 + 'px'
            right: defaultPosition
        });


    $('body').append(slider);

    $(document).scroll(function () {

        var scrollAmount = $(window).scrollTop();
        var documentHeight = $(document).height();
        var scrollPercent = (scrollAmount / documentHeight) * 100;

        var showSlider = function () {
            slider.animate(
                {
                    right: 0
                }
            );
        }

        var hideSlider = function () {
            slider.animate(
                {
                    right: defaultPosition
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