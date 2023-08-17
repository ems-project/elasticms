import Sortable from "sortablejs";

export default class JsonMenuNestedComponent {
    #hash;
    #tree;
    #element;
    #sortableLists = {};

    constructor (element) {
        this.#element = element;
        this.#tree = element.querySelector('.jmn-tree');
        this.#hash = element.dataset.hash;
        this.addClickListeners();
        this.getStructureRoot();
    }

    getStructureRoot() {
        this._fetch('/structure').then((json) => {
            if (!json.hasOwnProperty('structure')) return;
            this.#tree.innerHTML = json.structure;
            this._initSortables();
            this.loading(false);
        });
    }
    getStructureNode(node) {
        const children = node.querySelector('.jmn-children');
        children.classList.add('jmn-sortable');

        this._fetch(`/structure/${node.dataset.id}`).then((json) => {
            if (!json.hasOwnProperty('structure')) return;
            children.innerHTML = json.structure;
            this._initSortables();
            this.loading(false);
        });
    }

    loading(flag) {
        const element = this.#element.querySelector('.jmn-node-loading');
        element.style.display = flag ? 'flex' : 'none';
    }

    _fetch(path) {
        return fetch(['/component/json-menu-nested', this.#hash, path].join('/'), {
            method: 'GET',
            headers: { 'Content-Type': 'application/json'}
        }).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
        });
    }

    addClickListeners() {
        this.#element.addEventListener('click', (event) => {
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
            node.querySelectorAll(`.jmn-node`).forEach(child => child.remove() );
        } else {
            element.setAttribute('aria-expanded', 'true');
            this.getStructureNode(node);
        }
    }

    _initSortables() {
        const options = {
            group: 'shared',
            draggable: '.jmn-node',
            handle: '.jmn-btn-move',
            dragoverBubble: true,
            ghostClass: "jmn-move-ghost",
            chosenClass: "jmn-move-chosen",
            dragClass: "jmn-move-drag",
            animation: 10,
            fallbackOnBody: true,
            swapThreshold: 0.50
        }

        this.#element.querySelectorAll('.jmn-sortable').forEach((element) => {
            this.#sortableLists[element.id] = Sortable.create(element, options);
        });
    }
}