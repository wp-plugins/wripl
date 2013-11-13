(function ($) {
    $(document).ready(function () {
        $.each(WriplProperties.asyncScripts, function (index, value) {
            $.getScript(value, function () {
                console.log("success getting: " + value);
            });
        });
    });
})(jQuery);