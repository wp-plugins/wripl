(function ($) {

    const INIT_DONE_EVENT = "wripl-ajax-init-done";

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
                console.log(response);
                $("body").trigger( INIT_DONE_EVENT , response);
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
                        console.log('not logged in');
                        $("body").trigger( INIT_DONE_EVENT , response);
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