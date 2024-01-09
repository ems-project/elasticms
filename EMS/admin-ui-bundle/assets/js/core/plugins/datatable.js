import $ from 'jquery'
import 'datatables.net'
import 'datatables.net-bs'
import 'datatables.net-bs/css/dataTables.bootstrap.css'

export default class datatables {
  load (target) {
    const datatables = target.querySelectorAll('[data-datatable]');
    [].forEach.call(datatables, function (datatable) {
      $(datatable).DataTable(JSON.parse(datatable.dataset.datatable))
    })
  }
}
