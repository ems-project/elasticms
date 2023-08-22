import Sortable from "sortablejs";
import ajaxModal from "../helper/ajaxModal";

export default class JsonMenuNestedComponent {
    id;
    #hash;
    #tree;
    #element;
    #sortableLists = {};
    #loadIds = [];

    constructor (element) {
        this.id = element.id;
        this.#element = element;
        this.#tree = element.querySelector('.jmn-tree');
        this.#hash = element.dataset.hash;
        this.addClickListeners();
        this.load();
    }

    load() {
        this.post('/structure', {
            load_ids: this.#loadIds
        }).then((json) => {
            if (!json.hasOwnProperty('structure')) return;
            this.#tree.innerHTML = json.structure;
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
            const nodeId = node ? node.dataset.id : '_root';

            switch (true) {
                case element.classList.contains('jmn-btn-add'):
                    this.onClickButtonAdd(nodeId, element.dataset.add);
                    break;
                case element.classList.contains('jmn-btn-edit'):
                    this.onClickButtonEdit(nodeId);
                    break;
                case element.classList.contains('jmn-btn-delete'):
                    this.onClickButtonDelete(nodeId);
                    break;
                case element.classList.contains('jmn-btn-collapse'):
                    this.onClickButtonCollapse(element, node);
                    break;
            }
        }, false);
    }

    onClickButtonAdd(parentId, addId)
    {
        const url = ['/component/json-menu-nested', this.#hash, `item/${parentId}/add/${addId}`].join('/');
        this.ajaxModal(url);
    }
    onClickButtonEdit(itemId)
    {
        const url = ['/component/json-menu-nested', this.#hash, `item/${itemId}/edit`].join('/');
        this.ajaxModal(url);
    }

    onClickButtonDelete(nodeId)
    {
        this.loading(true);
        this.post(`item/${nodeId}/delete`).then((data) => {
            this.load();
        });
    }

    onClickButtonCollapse(button, node) {
        let expanded = button.getAttribute('aria-expanded');
        const nodeId = node.dataset.id;

        if ('true' === expanded) {
            button.setAttribute('aria-expanded', 'false');

            const childNodes = node.querySelectorAll(`.jmn-node`);
            const childIds = Array.from(childNodes).map((child) => child.dataset.id);
            childNodes.forEach((child) => child.remove());

            this.#loadIds = this.#loadIds.filter((id) => id !== nodeId && !childIds.includes(id));
        } else {
            button.setAttribute('aria-expanded', 'true');
            this.#loadIds.push(nodeId);
        }

        this.load();
    }

    onMove(event) {
        const dragged = event.dragged;
        const targetList = event.to;

        if (!dragged.dataset.hasOwnProperty('type') || !targetList.dataset.hasOwnProperty('types')) {
            return false;
        }

        const types = JSON.parse(targetList.dataset.types);

        return types.includes(dragged.dataset.type);
    }
    onMoveEnd(event) {
        const itemId = event.item.dataset.id;
        const targetComponentId = event.to.closest('.json-menu-nested-component').id;
        const fromComponentId = event.from.closest('.json-menu-nested-component').id;
        const position = event.newIndex;

        const toParentId = event.to.closest('.jmn-node').dataset.id;
        const fromParentId = event.from.closest('.jmn-node').dataset.id;

        if (targetComponentId === fromComponentId) {
            this.post(`item/${itemId}/move`, {
                fromParentId: fromParentId,
                toParentId: toParentId,
                position: position
            }).then(() => {  this.load(); });
        } else {
            window.jsonMenuNestedComponents[targetComponentId].loading(true);
            window.jsonMenuNestedComponents[fromComponentId].loading(true);
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
            swapThreshold: 0.50,
            onMove: (event) => { return this.onMove(event) },
            onEnd: (event) => { return this.onMoveEnd(event) },
        }

        this.#element.querySelectorAll('.jmn-sortable').forEach((element) => {
            this.#sortableLists[element.id] = Sortable.create(element, options);
        });
    }

    ajaxModal(url)
    {
        let handlerClose = () => {
            this.load();
            ajaxModal.modal.removeEventListener('ajax-modal-close', handlerClose);
        };

        ajaxModal.load({ 'url': url }, (json) => {
            if (!json.hasOwnProperty('success') || !json.success) return;
            if (json.hasOwnProperty('load')) {
                this.#loadIds.push(json.load);
            }
        });
        ajaxModal.modal.addEventListener('ajax-modal-close', handlerClose)
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