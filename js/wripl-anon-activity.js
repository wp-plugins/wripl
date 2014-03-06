var WriplAnonActivityEvents = {
    'INITIALISED': 'wripl-anonymous-activity-initialised',
    'ERROR': 'wripl-anonymous-activity-error'
};

(function ($) {

    var init = function () {

        var activitiesEndpoint = WriplProperties.apiBase + "/anonymous/activities";
        var activitiesUpdateEndpoint = WriplProperties.apiBase + "/anonymous/activity-update";

        var parameters = {
            key: WriplProperties.key,
            path: WriplProperties.path
        };

        $.ajax({
            type: 'GET',
            url: activitiesEndpoint,
            data: parameters,
            contentType: "application/json",
            dataType: 'jsonp'
        })
            .done(function (response) {

                if (response.activity_hash_id) {
                    wripl.main(
                        {
                            activityHashId: response.activity_hash_id,
                            endpoint: activitiesUpdateEndpoint
                        }
                    );
                }

                $("body").trigger(WriplAnonActivityEvents.INITIALISED);
            })

            .fail(function (xhr, ajaxOptions, thrownError) {
                console.log("Aw snap! Something went wrong: " + thrownError);
                $("body").trigger(WriplAnonActivityEvents.ERROR, xhr);
            });
    };

    $(document).ready(function () {
        init();
    });

})(jQuery);