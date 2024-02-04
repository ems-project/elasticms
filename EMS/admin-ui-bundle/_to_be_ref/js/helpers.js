'use strict';

window.objectPickerListeners = function(objectPicker, maximumSelectionLength){
    const type = objectPicker.data('type');
    const dynamicLoading = objectPicker.data('dynamic-loading');
    const searchId = objectPicker.data('search-id');
    const querySearch = objectPicker.data('query-search');

    let params = {
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    };

    if(maximumSelectionLength) {
        params.maximumSelectionLength = maximumSelectionLength;
    }
    else if(objectPicker.attr('multiple')) {
        params.allowClear = true;
        params.closeOnSelect = false;
    }

    if (dynamicLoading) {
        //params.minimumInputLength = 1,
        params.ajax = {
            url: object_search_url,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    type: type,
                    searchId: searchId,
                    querySearch: querySearch
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        };
    }

    objectPicker.select2(params);
};

window.requestNotification = function(element, tId, envName, ctId, id){
    const data = { templateId : tId, environmentName : envName, contentTypeId : ctId, ouuid : id};
    window.ajaxRequest.post(element.getAttribute("data-url") , data, 'modal-notifications');
};
