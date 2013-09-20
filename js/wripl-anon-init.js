var WriplEvents = {
    'INIT_START': 'wripl-anonymous-initialisation-start',
    'INIT_COMPLETE': 'wripl-anonymous-initialisation-complete',
    'INIT_ERROR': 'wripl-anonymous-initialisation-error'
};

console.log('wripl-anon-init.js');

(function ($) {

    $(document).ready(function () {
        console.log('calling wripl anon init');
        init();
    });

    var init = function () {

        $("body").trigger(WriplEvents.INIT_START);

        var activitiesEndpoint = WriplProperties.apiBase + "/anonymous/activities";

        var parameters = {
            key: WriplProperties.key,
            path: WriplProperties.path
        };

        console.dir(parameters);

        $.ajax({
            type: 'GET',
            url: activitiesEndpoint,
            data: parameters,
            contentType: "application/json",
            dataType: 'jsonp'
        })
            .done(function (response) {

                console.dir(response);

                if (response.activity_hash_id) {
                    wripl.main(
                        {
                            activityHashId: response.activity_hash_id,
                            endpoint: WriplProperties.apiBase + "/anonymous/activity-update"
                        }
                    );
                }
                getRecommendations();
            })

            .fail(function (xhr, ajaxOptions, thrownError) {
                console.log("Aw snap! Something went wrong: " + thrownError);
                $("body").trigger(WriplEvents.INIT_ERROR, xhr);
            });
    };

    var getRecommendations = function () {

        var recommendationsEndpoint = WriplProperties.apiBase + "/anonymous/recommendations";

        var parameters = {
            key: WriplProperties.key
        };

        console.log(parameters);

        $.ajax({
            type: 'GET',
            url: recommendationsEndpoint,
            data: parameters,
            contentType: "application/json",
            dataType: 'jsonp'
        })
            .done(function (response) {

                console.dir(response);

                if (response) {
                    console.log("response with recommendations:");
                    console.log(response);
                }

                $("body").trigger(WriplEvents.INIT_COMPLETE, { 'recommendations': response });
            })

            .fail(function (xhr, ajaxOptions, thrownError) {
                console.log("Aw snap! Something went wrong: " + thrownError);
                $("body").trigger(WriplEvents.INIT_ERROR, xhr);
            });
    };

})(jQuery);