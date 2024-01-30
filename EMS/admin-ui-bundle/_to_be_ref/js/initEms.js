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

    function activeMenu() {
        //try to find which side menu elements to activate
        const currentMenuLink = $('section.sidebar ul.sidebar-menu a[href="' + window.location.pathname + window.location.search + '"]');

        if ( currentMenuLink.length > 0 ) {
            currentMenuLink.last().parents('li').addClass('active');
        }
        else {
            $('#side-menu-id').each(function(){
                $('#'+$(this).data('target')).parents('li').addClass('active');
            });
        }
    }


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

    function initAjaxFormSave() {
        $('button[data-ajax-save-url]').each(function(){
            const button = $(this);
            const form = button.closest('form');

            const ajaxSave = function(event){
                event.preventDefault();

                const formContent = form.serialize();
                window.ajaxRequest.post(button.data('ajax-save-url'), formContent)
                    .success(function(message) {
                        let response = message;
                        if ( ! response instanceof Object ) {
                            response = jQuery.parseJSON( message );
                        }

                        $('.has-error').removeClass('has-error');

                        $(response.errors).each(function(index, item){
                            $('#'+item.propertyPath).parent().addClass('has-error');
                        });
                    });
            };

            button.on('click', ajaxSave);

            $(document).keydown(function(e) {
                let key = undefined;
                const possible = [ e.key, e.keyIdentifier, e.keyCode, e.which ];

                while (key === undefined && possible.length > 0)
                {
                    key = possible.pop();
                }

                if (typeof key === "number" && ( 115 === key || 83 === key ) && (e.ctrlKey || e.metaKey) && !(e.altKey))
                {
                    ajaxSave(e);
                    return false;
                }
                return true;

            });

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

    function initPostButtons() {
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('core-post-button')) {
                e.preventDefault();

                let button = e.target;
                let postSettings = JSON.parse(button.dataset.postSettings)
                let url = button.href;

                let f = postSettings.hasOwnProperty('form') ? document.getElementById(postSettings.form) :  document.createElement('form');

                if (postSettings.hasOwnProperty('form')) {
                    let my_tb=document.createElement('INPUT');
                    my_tb.style.display='none';
                    my_tb.type='TEXT';
                    my_tb.name='source_url';
                    my_tb.value= url;
                    f.appendChild(my_tb);

                    if (postSettings.action) {
                        f.action=JSON.parse(postSettings.action);
                    }
                } else {
                    f.style.display='none';
                    f.method='post';
                    f.action=url;
                    button.parentNode.appendChild(f);
                }

                if (postSettings.hasOwnProperty('value') && postSettings.hasOwnProperty('name')) {
                    let my_tb=document.createElement('INPUT');
                    my_tb.style.display='none';
                    my_tb.type='TEXT';
                    my_tb.name=JSON.parse(postSettings.name);
                    my_tb.value=JSON.parse(postSettings.value);
                    f.appendChild(my_tb);
                }

                f.submit();
            }
        });
    }


    $(document).ready(function() {
        activeMenu();
        closeModalNotification();
        toggleMenu();
        initSearchForm();
        autoOpenModal(queryString());
        initAjaxFormSave();
        initJsonMenu();
        intAjaxModalLinks();
        initPostButtons();

        window.dispatchEvent(new CustomEvent('emsReady'));
    });

}));
