export default function searchRelease() {
    $(document).on('change', '#categories_filter input[type="checkbox"]', function () {
        $(this).closest('form').trigger('submit');
    })
}