var WriplMostEngagingEvents = {
    'INIT_START': 'wripl-anonymous-most-engaging-initialisation-start',
    'INIT_COMPLETE': 'wripl-anonymous-most-engaging-initialisation-complete',
    'INIT_ERROR': 'wripl-anonymous-most-engaging-initialisation-error',
    'TEMPLATE_FETCHED': 'wripl-most-engaging-template-fetched'
};

(function ($) {

    $(document).ready(function () {

        var getRecommendations = function () {

            var recommendationsEndpoint = WriplProperties.apiBase + "/anonymous/most-engaging";
            var parameters = {
                key: WriplProperties.key
            };

            $.ajax({
                type: 'GET',
                url: recommendationsEndpoint,
                data: parameters,
                contentType: "application/json",
                dataType: 'jsonp'
            })
                .done(function (response) {
                    // Only trigger an INIT_COMPLETE if there are recommendations (array & greater that 0).
                    if (Object.prototype.toString.call(response) === '[object Array]' && response.length > 0) {
                        console.log(response.length + " most engaging.");
                        $("body").trigger(WriplMostEngagingEvents.INIT_COMPLETE, { 'mostEngaging': response });
                    } else {
                        $("body").trigger(WriplMostEngagingEvents.INIT_ERROR);
                    }
                })

                .fail(function (xhr, ajaxOptions, thrownError) {
                    console.log("Aw snap! Something went wrong: " + thrownError);
                    $("body").trigger(WriplMostEngagingEvents.INIT_ERROR, xhr);
                });
        };

        $("body").bind(WriplAnonActivityEvents.INITIALISED, getRecommendations);

    });

})(jQuery);