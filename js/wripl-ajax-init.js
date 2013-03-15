console.log('wripl-ajax-init.js');
(function ($) {

    const INIT_LOGGED_IN_EVENT = "wripl-ajax-init-logged-in";
    const INIT_NOT_LOGGED_IN_EVENT = "wripl-ajax-init-not-logged-in";
    const INIT_ERROR_EVENT = "wripl-ajax-init-error";


    $(document).ready(function () {
        console.log('calling wripl init');
        init();
    });

    var init = function () {
        console.log('wripl init called');

        $.post(
            WriplAjaxProperties.ajaxUrl,
            {
                action:'wripl-ajax-init',
                path:WriplAjaxProperties.path
            }
        ).done(function(response) {
                console.log('init post success');
                console.log(response);

                $("body").trigger( INIT_LOGGED_IN_EVENT , response);
                if(response.activityHashId) {
                    wripl.main(response);
                }
            }
        ).fail(function(response){
                console.log('init post fail');
                console.log(response);

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