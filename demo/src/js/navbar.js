export class NavBar {
    constructor() {
    }

    activateBestItem() {
        if (window.location.pathname === '/' || window.location.pathname === document.documentElement.getAttribute('data-base-url')) {
            return;
        }

        let bestItems = document.querySelectorAll('header nav.navbar a[href^="' + window.location.pathname + window.location.search + '"]')
        if (bestItems.length <= 0) {
            bestItems = document.querySelectorAll('footer .navbar-nav a[href^="' + window.location.pathname + window.location.search + '"]')
            if (bestItems.length <= 0) {
                return;
            }
        }

        let activeMessage = '(active)'
        const navbar = document.querySelector('nav.navbar[data-active-message]');
        if (null !== navbar) {
            activeMessage = navbar.getAttribute('data-active-message');
        }

        const lastItem = bestItems[bestItems.length-1];
        const node = document.createElement("span");
        node.classList.add('sr-only');
        const textNode = document.createTextNode(activeMessage);
        const spaceNode = document.createTextNode(' ');
        node.appendChild(textNode);
        lastItem.appendChild(spaceNode);
        lastItem.appendChild(node);

        for (let current = lastItem.parentNode; 'NAV' !== current.nodeName ; current = current.parentNode) {
            if ('FOOTER' === current.nodeName) {
                break;
            }

            if ('LI' !== current.nodeName) {
                continue;
            }
            current.classList.add('active');

        }
    }
}
