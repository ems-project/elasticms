'use strict';

/*
 * This function initialized the elasticms admin interface
 *
 */
import EmsListeners from "./EmsListeners";
import ajaxModal from "../../js/core/helpers/ajaxModal";

(function(factory) {
    "use strict";

    if ( typeof define === "function" && define.amd ) {
        // AMD. Register as an anonymous module.
        define([
            "jquery",
        ], factory );
    } else {
        // Browser globals
        factory( window.jQuery );
    }

}(function($) {

    function closeModalNotification() {
        $('#modal-notification-close-button').on('click', function(){
            $('#modal-notifications .modal-body').empty();
            $('#modal-notifications').modal('hide');
        });
    }

    function intAjaxModalLinks() {
        let ajaxModalLinks = document.querySelectorAll('a[data-ajax-modal-url]');
        [].forEach.call(ajaxModalLinks, function (link) {
            link.onclick = (event) => {
                ajaxModal.load({
                    url: event.target.dataset.ajaxModalUrl,
                    size: event.target.dataset.ajaxModalSize
                }, (json) => {
                    if (json.hasOwnProperty('success') && json.success === true) {
                        location.reload();
                    }
                });
            }
        });
    }


    $(document).ready(function() {
        closeModalNotification();
        intAjaxModalLinks();
    });

}));
