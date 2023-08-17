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
        this.get('/structure').then((json) => {
            if (!json.hasOwnProperty('structure')) return;
            this.#tree.innerHTML = json.structure;
            this._initSortables();
            this.loading(false);
        });
    }
    getStructureNode(node) {
        const children = node.querySelector('.jmn-children');
        children.classList.add('jmn-sortable');

        this.get(`/structure/${node.dataset.id}`).then((json) => {
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

    addClickListeners() {
        this.#element.addEventListener('click', (event) => {
            const element = event.target;
            const node = element.parentElement.closest('.jmn-node');
            switch (true) {
                case element.classList.contains('jmn-btn-delete'):
                    this.onClickButtonDelete(node.dataset.id);
                    break;
                case element.classList.contains('jmn-btn-collapse'):
                    this.onClickButtonCollapse(element, node);
                    break;
            }
        }, false);
    }

    onClickButtonDelete(nodeId)
    {
        this.loading(true);
        this.post(`item/${nodeId}/delete`).then((data) => {
            console.debug(data);
            this.loading(false);
        });
    }

    onClickButtonCollapse(button, node) {
        let expanded = button.getAttribute('aria-expanded');

        if ('true' === expanded) {
            button.setAttribute('aria-expanded', 'false');
            node.querySelectorAll(`.jmn-node`).forEach(child => child.remove() );
        } else {
            button.setAttribute('aria-expanded', 'true');
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

    async get(path) {
        this.loading(true);
        const url = ['/component/json-menu-nested', this.#hash, path].join('/');
        const response = await fetch(url, {
            method: "GET",
            headers: { 'Content-Type': 'application/json'},
        });
        return response.json();
    }
    async post(path, data = {}) {
        this.loading(true);
        const url = ['/component/json-menu-nested', this.#hash, path].join('/');
        const response = await fetch(url, {
            method: "POST",
            headers: { 'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        return response.json();
    }
}