/**
 * Load the ajax search
 *
 * Dom element with the class ajax-search-hide will be hidden
 * Dom element with the class ajax-search-remove will be deleted
 * Dom element with the class ajax-search-empty will be cleaned
 * Dom element with an data-ajax-search-replace attribute will see its content replace by the value of this attribute
 * Dom element with an data-ajax-search-load-more-url attribute will do an ajax query with the url defined in this attribute
 * Dom form.data-ajax-search-form on change
 *
 * Dom element with aria-live will be updated by the result of ajax query (loop on all json atributes starting by the string 'html_'
 *      the content is replaced if the role="status" or if it's change event on the search form
 *      the content is append if it's a "load more" event (and the role != "status")
 *
 * Dom element with data-ajax-search-loading-hide class are hidden during ajax calls and displayed after
 * Dom element with data-ajax-search-loading-show class are show during ajax call and hiddent after (i.e. loaders)
 *
 */
export default function ajaxSearch(submitOnKeyStroke = false) {
    const $ = require('jquery');
    let requestInProgress = false;
    let nextRequest;
    let pushData = null;

    $('.ajax-search-hide').hide();
    $('.ajax-search-empty').empty();
    $('.ajax-search-remove').remove();

    const updateDom = function(msg) {
        for (const key of Object.keys(msg)) {
            if (key.startsWith("html_")) {
                const html = $('<div/>').html(msg[key]);
                html.find('[aria-live]').each(function() {
                    const item = $(this);
                    const id = item.attr('id');
                    if (!id) {
                        console.log('aria-live without id!');
                        return;
                    }
                    const target = $('#'+id);
                    if (msg.page == 0 || target.attr('role') === 'status') {
                        target.html(item.contents());
                    }
                    else {
                        target.append(item.contents());
                    }
                });

                if (msg.title) {
                    $('h1').text(msg.title);
                }

                if (msg.title_header) {
                    document.title = $('<textarea />').html(msg.title_header).text();
                }

                if (msg.load_more_path && msg.load_more_path !== '') {
                    $('[data-ajax-search-load-more-url]').show().attr('data-ajax-search-load-more-url', msg.load_more_path);
                }
                else {
                    $('[data-ajax-search-load-more-url]').hide().attr('data-ajax-search-load-more-url', '');
                }

            }
        }
    };


    const doAjaxRequest = function(url) {

        if (requestInProgress) {
            nextRequest = url;
            return;
        }
        requestInProgress = true;

        $('.data-ajax-search-loading-hide').hide();
        $('.data-ajax-search-loading-show').show();

        $.ajax(url)
            .done(function(msg) {
                updateDom(msg);
            })
            .fail(function() {
                alert( "error with this page" );
            })
            .always(function(){
                requestInProgress = false;
                if (nextRequest && nextRequest !== url) {
                    const newUrl = nextRequest;
                    nextRequest = false;
                    doAjaxRequest(newUrl);
                    return;
                }
                nextRequest = false;
                $('.data-ajax-search-loading-hide').show();
                $('.data-ajax-search-loading-show').hide();
            });
    };

    const loadMore = function(){
        const loadMoreButton = $(this);
        const url = loadMoreButton.attr('data-ajax-search-load-more-url');
        doAjaxRequest(url);
    };

    $('[data-ajax-search-replace]').each(function(){
        const item = $(this);
        const replacement = $(item.data('ajax-search-replace'));
        replacement.find('[data-ajax-search-load-more-url]').on('click', loadMore);

        item.empty().append(replacement);
    });


    const forms = $('[data-ajax-search-form]');

    const formChangeFunction = function (event) {
        formSubmitFunction(event, false);
    };

    const formSubmitFunction = function (event, clearAriaLive = true) {
        event.preventDefault();

        const arias = [];
        $('[aria-live]').each(function() {
            const id = $(this).attr('id');
            if (id) {
                arias[id] = ($(this).html());
            }
        });
        pushData = {
            title: $('h1').text(),
            documentTitle: document.title,
            arias: arias,
            loadMorePath: $('[data-ajax-search-load-more-url]:first-child').attr('data-ajax-search-load-more-url'),
        };

        if (clearAriaLive) {
            $('[aria-live]').empty();
        }
        else {
            $('[aria-live]').not('[role=status]').empty();
        }

        history.pushState(pushData, pushData.title, forms.attr('action') + '?' + forms.serialize());
        doAjaxRequest(forms.attr('data-ajax-search-form') + '?' + forms.serialize());
    };

    window.onpopstate = function(event) {
        if (null === pushData) {
            return;
        }

        $('[aria-live]').each(function() {
            const id = $(this).attr('id');
            if (id && pushData.arias[id]) {
                $('#'+id).html(pushData.arias[id]);
            }
        });
        $('h1').text(pushData.title);
        document.title = pushData.documentTitle;

        if (pushData.loadMorePath && pushData.loadMorePath !== '') {
            $('[data-ajax-search-load-more-url]').show().attr('data-ajax-search-load-more-url', pushData.loadMorePath);
        }
        else {
            $('[data-ajax-search-load-more-url]').hide().attr('data-ajax-search-load-more-url', '');
        }

        pushData = event.state;
    }


    forms.change(formChangeFunction);
    forms.submit(function(event) {
        event.preventDefault();
    });
    if (submitOnKeyStroke) {
        //The following lines allow submit on key stroke
        forms.find("input[type=text]").on('input', formSubmitFunction);
        forms.find("textarea").on('input', formSubmitFunction);
    }

}