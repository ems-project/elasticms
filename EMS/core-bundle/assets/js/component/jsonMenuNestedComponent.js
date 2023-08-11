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
        let element = row ? row : this.#element;
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
        let row = element.parentElement.closest('.jmn-row');
        let id = row.dataset.id;

        let expanded = element.getAttribute('aria-expanded');

        if ('true' === expanded) {
            element.setAttribute('aria-expanded', 'false');
            let children = row.querySelector(`.jmn-children`);
            if (children) { row.removeChild(children); }
        } else {
            element.setAttribute('aria-expanded', 'true');
            this.getStructure(row);
        }
    }

}