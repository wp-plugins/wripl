var WriplRecommendationEvents = {
    'INIT_START': 'wripl-anonymous-recommendation-initialisation-start',
    'INIT_COMPLETE': 'wripl-anonymous-recommendation-initialisation-complete',
    'INIT_ERROR': 'wripl-anonymous-recommendation-initialisation-error',
    'TEMPLATE_FETCHED': 'wripl-recommendation-template-fetched'
};

(function ($) {

    $(document).ready(function () {

        var getRecommendations = function () {
            var recommendationsEndpoint = WriplProperties.apiBase + "/anonymous/recommendations";
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
                        console.dir(response);
                        console.log(response.length + " recommendations.");
                        $("body").trigger(WriplRecommendationEvents.INIT_COMPLETE, { 'recommendations': response });
                    } else {
                        $("body").trigger(WriplRecommendationEvents.INIT_ERROR);
                    }
                })

                .fail(function (xhr, ajaxOptions, thrownError) {
                    console.log("Aw snap! Something went wrong: " + thrownError);
                    $("body").trigger(WriplRecommendationEvents.INIT_ERROR, xhr);
                });
        };

        $("body").bind(WriplAnonActivityEvents.INITIALISED, getRecommendations);

    });

})(jQuery);