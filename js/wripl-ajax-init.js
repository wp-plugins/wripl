(function ($) {

    $(document).ready(function () {

        //add listeners here?
        $("#wripl-ajax-container").bind('wriplEvent', function (e) {
            //console.log(e)
        });

        init();

    });

    var init = function () {
        $.post(
            WriplAjaxProperties.ajaxUrl,
            {
                action:'wripl-ajax-init',
                path:WriplAjaxProperties.path
            }
        ).done(function(response) {
                console.log(response);
                $("#wripl-ajax-container").trigger('wriplEvent', response);
            }
        ).fail(function(response){
                switch(response.status)
                {
                    case 403:
                        console.log('not logged in');
                        break;
                    default:
                        //$("#wripl-ajax-container").trigger('wriplEvent', response);
                        console.log('in some error state. message : ' + response.responseText);
                        break;
                }
            }
        );


    }

})(jQuery);