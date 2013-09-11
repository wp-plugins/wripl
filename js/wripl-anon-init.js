console.log('wripl-anon-init.js');
(function ($) {

    var events = {
        'INIT_EVENT': 'wripl-anonymous-initialisation',
        'INIT_ERROR_EVENT': 'wripl-anonymous-initialisation-error',
        'INIT_COMPLETE': 'wripl-anonymous-initialisation-complete',
        'START_SPINNING_LOGO': 'wripl-start-spinning-logo'
    };

    $(document).ready(function () {
        console.log('calling wripl anon init');
        init();
    });

    var init = function () {

        var url = WriplProperties.apiBase + "/anonymous/activities";

        var params = {
            key: WriplProperties.key,
            path: WriplProperties.path
        };

        $.ajax({
            type: 'GET',
            url: url,
            data: params,
            async: false,
            contentType: "application/json",
            dataType: 'jsonp',
            success: function (response) {

                if (typeof(response) !== "object") {

                    console.log('init get success - but response is not an object');
                    $("body").trigger(events.INIT_ERROR_EVENT, response);

                    // return early if response is not an object.
                    return;
                }
                console.log('GET called against: ' + url);
                console.dir(response);

                $("body").trigger(events.START_SPINNING_LOGO);
            }
        })
            .done(function (response) {

                if (response.piwikScript) {
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src = response.piwikScript;
                    $("body").append(script);
                } else {
                    console.log('no piwik script');
                }

                $("body").trigger(events.INIT_COMPLETE, response);

                if (response.activity_hash_id) {
                    wripl.main(
                        {
                            activityHashId : response.activity_hash_id,
                            endpoint : WriplProperties.apiBase + "/anonymous/activity-update"
                        }
                    );
                }
            })
            .fail(function (response) {
                $("body").trigger(events.INIT_ERROR_EVENT, response);
            })
            .always(function (response) {
                console.log('always!!!');
            });
    };



})(jQuery);