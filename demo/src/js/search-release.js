export default function searchRelease() {
    const $ = require('jquery');

    $(document).on('change', '#categories_filter input[type="checkbox"]', function () {
        $(this).closest('form').trigger('submit');
    })
}