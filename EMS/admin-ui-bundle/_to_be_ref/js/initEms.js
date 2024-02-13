'use strict';

/*
 * This function initialized the elasticms admin interface
 *
 */
import EmsListeners from "./EmsListeners";
import JsonMenu from "./module/jsonMenu";
import JsonMenuNested from "./module/jsonMenuNested";
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

    function toggleMenu() {
        $('.toggle-button').on('click', function(){
            const toggleTex = $(this).data('toggle-contain');
            const text=$(this).html();
            $(this).html(toggleTex);
            $(this).data('toggle-contain', text);
        });
    }

    function autoOpenModal(queryString) {
        if(queryString.open) {
            $('#content_type_structure_fieldType'+queryString.open).modal('show');
        }
    }

    function initSearchForm() {

        $('#add-search-filter-button').on('click', function(e) {
            // prevent the link to scroll to the top ("#" anchor)
            e.preventDefault();

            const $listFilters = $('#list-of-search-filters');
            const prototype = $listFilters.data('prototype');
            const index = $listFilters.data('index');
            // Replace '__name__' in the prototype's HTML to
            // instead be a number based on how many items we have
            const newForm = $(prototype.replace(/__name__/g, index));

            // increase the index with one for the next item
            $listFilters.data('index', index + 1);

            //attach listeners to the new DOM element
            new EmsListeners(newForm.get(0));
            $listFilters.append(newForm);

        });
    }

    function initJsonMenu() {
        $('.json_menu_editor_fieldtype').each(function(){ new JsonMenu(this); });

        let jsonMenuNestedList = [];
        $('.json-menu-nested').each(function () {
            let menu = new JsonMenuNested(this);
            jsonMenuNestedList[menu.getId()] = menu;
        });
        window.jsonMenuNested = jsonMenuNestedList;
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
        toggleMenu();
        initSearchForm();
        autoOpenModal(queryString());
        initJsonMenu();
        intAjaxModalLinks();

        window.dispatchEvent(new CustomEvent('emsReady'));
    });

}));
