export default function externalLink(selector= 'footer .nav a[href^="//"], #main-content a[href^="http"], footer .nav a[href^="http"], #main-content a[href^="//"]'
                                     , notSelector='[href*="' + window.location.hostname + '"],.not-external') {
    $(selector)
        .not(notSelector)
        .attr('target', '_blank')
        .addClass('ems-external-link');
}