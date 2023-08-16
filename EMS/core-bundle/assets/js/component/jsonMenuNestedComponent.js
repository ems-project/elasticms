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
        this.getStructure();
    }

    getStructure(node = null) {
        this.loading(true);
        let element = node ? node.querySelector('.jmn-children') : this.#tree;


        let nodeId = node ? node.dataset.id : null;
        let path = nodeId ? `structure/${nodeId}` : 'structure';

        this._fetch(path).then((json) => {
            if (json.hasOwnProperty('rows')) {
                json.rows.forEach((row) => element.innerHTML += row);
            }

            this.sortableCreate(element);
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
            this.sortableRemove(node);
            node.querySelectorAll(`.jmn-node`).forEach(child => child.remove() );
        } else {
            element.setAttribute('aria-expanded', 'true');
            this.getStructure(node);
        }
    }

    sortableCreate(element) {
        const options = {
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
        }

        const create = (element, id) => {
            this.#sortableLists[id] = Sortable.create(element, options);
        }

        create(element, element.dataset.id ?? 'tree');
        element.querySelectorAll('.jmn-children-empty').forEach((emptyChildren) => {
            create(emptyChildren, emptyChildren.parentElement.dataset.id);
        } );
    }
    sortableRemove(node)
    {
        let remove = (node) => {
            let nodeId = node.dataset.id

            if (this.#sortableLists.hasOwnProperty(nodeId)) {
                const list = this.#sortableLists[nodeId];
                list.destroy();
                delete this.#sortableLists[nodeId];
            }
        }
        remove(node);
        node.querySelectorAll(`.jmn-node`).forEach(child => remove(child));
    }
}