export default function newsFilters() {
    const $newsfilters = $('.news-filters');
    let activeFilters = [];
    if ($newsfilters.length) {
        $('[type="checkbox"]',$newsfilters).change(function() {
            const $parentBtn = $(this).parent();
            const checkedValue = $(this).val();
            if($(this).is(':checked')) {
                $parentBtn.addClass('active')
                activeFilters.push(checkedValue)
            } else {
                $parentBtn.removeClass('active')
                activeFilters = $.grep(activeFilters, function(value) {
                    return value !== checkedValue;
                })
            }
            if(activeFilters.length) {
                $('[data-tag]').hide();
                $.each(activeFilters, function( index, value ) {
                    $('[data-tag="'+value+'"]').show();
                });
            } else {
                $('[data-tag]').show();
            }
        })
    }
}