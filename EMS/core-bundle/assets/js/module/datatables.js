const jquery = require('jquery');
require('datatables.net');
require('datatables.net-bs');

export default class datatables {
    constructor(target) {
        const datatables = target.querySelectorAll('[data-datatable]');
        this.loadDatatables(datatables)
    }

    loadDatatables(datatables) {
        [].forEach.call(datatables, function(element) {
            const datatable = jquery(element).DataTable(JSON.parse(element.dataset.datatable));
            datatable.on('draw', () => new EmsListeners(element))
        });
    }
}
