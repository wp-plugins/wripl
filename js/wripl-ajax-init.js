(function ($) {

    const INIT_LOGGED_IN_EVENT = "wripl-ajax-init-logged-in";
    const INIT_NOT_LOGGED_IN_EVENT = "wripl-ajax-init-not-logged-in";
    const INIT_ERROR_EVENT = "wripl-ajax-init-error";


    $(document).ready(function () {
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
//                console.log(response);
                $("body").trigger( INIT_LOGGED_IN_EVENT , response);
                if(response.activityHashId) {
                    wripl.main(response);
                }
            }
        ).fail(function(response){

                response.responseText = response.responseText || "{}";

                var responseTextObject = eval('(' + response.responseText + ')');

                if(responseTextObject.piwikScript) {
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src = responseTextObject.piwikScript;

                    $("body").append(script);
                }

                switch(response.status)
                {

                    case 403:
//                        console.log('not logged in');
                        $("body").trigger( INIT_NOT_LOGGED_IN_EVENT , response);
                        break;
                    default:
                        $("body").trigger(INIT_ERROR_EVENT, response);
                        break;
                }
            }
        );
    }


})(jQuery);