export default function cookiesBanner(Cookies) {
    if (Cookies.get('cookieBanner') == undefined) {
        $('#cookiesBanner').addClass('active');
    }

    $(document).on('click', '#cookiesBanner .close-banner', function() {
        $('#cookiesBanner').removeClass('active');
        Cookies.set('cookieBanner', true, { expires: 31 });

        return false;
    });
}