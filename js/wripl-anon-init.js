var WriplEvents = {
    'INIT_START': 'wripl-anonymous-initialisation-start',
    'INIT_COMPLETE': 'wripl-anonymous-initialisation-complete',
    'INIT_ERROR': 'wripl-anonymous-initialisation-error',
    'TEMPLATE_FETCHED': 'wripl-template-fetched'
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

        /*
            The following listens to see if any wripl template is fetched (and used) via  event "WriplEvents.TEMPLATE_FETCHED"
            the widget,
            the slider,
            or the end of content.

            If so it creates the qrDialog html and appends it to the body.

            subsequently a click event listener is added to each button with a class of ".go-cross-device-button"
         */
        $("body").bind(WriplEvents.TEMPLATE_FETCHED, function (e, response) {

            if (document.getElementById("wripl-qr-dialog")) {
                // do nothing as "wripl-qr-dialog" already exists
            } else {
                var qrDialog = $('<div id="wripl-qr-dialog"/>')
                    .appendTo('body')
                    .append("<p>Scan to continue on another device...</p>")
                    .prepend($('<img>', {
                        src: WriplProperties.pluginPath + 'images/go-mobile.png'
                    }))
                    .prepend($('<img>', {
                        src: WriplProperties.apiBase + '/anonymous/sync/qr.png?redirect=' + window.location
                    }));

                qrDialog.dialog({
                    autoOpen: false,
                    modal: true,
                    resizable: false,
                    closeText: "done",
                    draggable: false,
                    show: {
                        effect: "fade",
                        duration: 150
                    },
                    hide: {
                        effect: "fade",
                        duration: 150
                    },
                    buttons: {
                        "Done": function () {
                            $(this).dialog("close");
                        }
                    },
                    open: function () {
                        $("button").blur();            // remove the default autofocus
                    }
                });

                console.log(".dialog() called");

                $(".ui-dialog-titlebar").hide();

                // add the listener
                $('.wripl-ajax-container').on('click', '.go-cross-device-button', function () {
                    qrDialog
                        .data('link', WriplProperties.apiBase + '/anonymous/sync/qr.png?redirect=' + window.location)
                        .dialog('open');
                    return false;
                });
            }

        });
    };

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

                // Only trigger an INIT_COMPLETE if there are recommendations.
                if (response.length > 0) {
                    console.dir(response);
                    console.log(response.length + " recommendations.");
                    $("body").trigger(WriplEvents.INIT_COMPLETE, { 'recommendations': response });
                } else {
                    $("body").trigger(WriplEvents.INIT_ERROR);
                }
            })

            .fail(function (xhr, ajaxOptions, thrownError) {
                console.log("Aw snap! Something went wrong: " + thrownError);
                $("body").trigger(WriplEvents.INIT_ERROR, xhr);
            });
    };

})(jQuery);

