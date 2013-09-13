console.log('wripl-anon-init.js');
(function ($) {

    var events = {
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

        console.dir(params);

        $.ajax({
            type: 'GET',
            url: url,
            data: params,
            async: false,
            contentType: "application/json",
            dataType: 'jsonp'
        })
            .done(function (response) {

                $("body").trigger(events.START_SPINNING_LOGO);

                console.dir(response);

                if (typeof(response) !== "object") {
                    console.log('init get success - but response is not an object');
                    $("body").trigger(events.INIT_ERROR_EVENT, response);
                    return;
                }

                if (response.activity_hash_id) {
                    wripl.main(
                        {
                            activityHashId : response.activity_hash_id,
                            endpoint : WriplProperties.apiBase + "/anonymous/activity-update"
                        }
                    );
                }

                getRecommendations(response);
            })

            .fail(function (xhr, ajaxOptions, thrownError) {
                alert("Aw snap! Something went wrong: " + thrownError);
                $("body").trigger(events.INIT_ERROR_EVENT, xhr);
            })

            .always(function (response) {
                console.log('always!');
            });
    };

    var getRecommendations = function (response, maxRecommendations) {

        var endpoint = "http://api.wripl.dev/v0.1/anonymous/recommendations";

        console.log("response.activity_hash_id: " + response.activity_hash_id);
        console.log('simulating the call to /anonymous/recommendations against: ' +endpoint);

        var params = {
            key: WriplProperties.key,
            max: WriplWidgetProperties.maxRecommendations
        };

        console.log(params);

        $.ajax({
            type: 'GET',
            url: endpoint,
            data: params,
            contentType: "application/json",
            dataType: 'jsonp'
        })
            .done(function (response) {
                console.dir(response);
                if (typeof(response) !== "object") {
                    console.log('getRecommendations() success - but response is not an object');
                    $("body").trigger(events.INIT_ERROR_EVENT, response);
                    return;
                }

                if (response) {
                    console.log("response with recommendations:");
                    console.log(response);
                }

                $("body").trigger(events.INIT_COMPLETE, { 'recommendations': response });
            })

            .fail(function (xhr, ajaxOptions, thrownError) {
                alert("Aw snap! Something went wrong: " + thrownError);
                $("body").trigger(events.INIT_ERROR_EVENT, xhr);
            });
    };

})(jQuery);