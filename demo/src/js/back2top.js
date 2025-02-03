/**
 * Back to top JS
 */
export default function back2top() {
    const $backToTop = $('#back2top');
    let offsetTop = 60;
    const mainContent = document.getElementById('content');
    if (mainContent) {
        offsetTop = mainContent.offsetTop;
    }

    const scrollCallback = function() {
        if ($(this).scrollTop() > offsetTop) {
            $backToTop.fadeIn();
        } else {
            $backToTop.fadeOut();
        }
    };

    // appear on scroll
    if ($backToTop.length) {
        scrollCallback();
        $(window).scroll(scrollCallback);
    }

    // smooth scroll
    $backToTop.click(function(e) {
        e.preventDefault();
        const target = $('body');
        $('html, body').animate({
            scrollTop: target.offset().top
        }, 500, function() {
            target.attr('tabindex', '-1').focus(); // Set focus on body
        });
    });
}
