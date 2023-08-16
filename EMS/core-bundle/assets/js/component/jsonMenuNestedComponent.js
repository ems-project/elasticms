import Sortable from "sortablejs";

export default class JsonMenuNestedComponent {
    #hash;
    #element;

    constructor (element) {
        this.#element = element;
        this.#hash = element.dataset.hash;
        this.addClickListeners();
        this.getStructure();
    }



    getStructure(row = null) {
        let element = row ? row : this.#element.querySelector('.jmn-wrapper');
        let treeClass = row ? 'jmn-children' : 'jmn-tree';

        let tree = document.createElement('div');
        tree.classList.add(treeClass);

        let currentTree = element.querySelector(`.${treeClass}`);
        if (currentTree) element.removeChild(currentTree);

        let path = row ? `structure/${row.dataset.id}` : 'structure';

        this._fetch(path).then((json) => {
            if (json.hasOwnProperty('rows')) {
                json.rows.forEach((row) => tree.innerHTML += row);
            }
        });

        element.appendChild(tree);
        this.initSortable(element.querySelector(`.${treeClass}`));
    }

    _fetch(path) {
        return fetch(['/component/json-menu-nested', this.#hash, path].join('/'), {
            method: 'GET',
            headers: { 'Content-Type': 'application/json'}
        }).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
        });
    }

    initSortable(element) {
        Sortable.create(element, {
            group: 'shared',
            draggable: '.jmn-node',
            handle: '.jmn-btn-move',
            dragoverBubble: true,
            ghostClass: "jmn-move-ghost",
            chosenClass: "jmn-move-chosen",
            dragClass: "jmn-move-drag",
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65
        });
    }

    addClickListeners() {
        window.addEventListener('click', (event) => {
            const element = event.target;
            switch (true) {
                case element.classList.contains('jmn-btn-collapse'):
                    this.onClickButtonCollapse(element);
            }
        }, false);
    }

    onClickButtonCollapse(element) {
        let node = element.parentElement.closest('.jmn-node');
        let expanded = element.getAttribute('aria-expanded');

        if ('true' === expanded) {
            element.setAttribute('aria-expanded', 'false');
            let children = node.querySelector(`.jmn-children`);
            if (children) { node.removeChild(children); }
        } else {
            element.setAttribute('aria-expanded', 'true');
            this.getStructure(node);
        }
    }
}