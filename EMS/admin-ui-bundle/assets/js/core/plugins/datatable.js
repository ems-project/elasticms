import $ from 'jquery'
import 'datatables.net'
import 'datatables.net-bs'
import 'datatables.net-bs/css/dataTables.bootstrap.css'
import '../../../css/core/plugins/datatable.scss'

class Datatable {
  load (target) {
    const datatables = target.querySelectorAll('[data-datatable]');
    [].forEach.call(datatables, function (datatable) {
      $(datatable).DataTable(JSON.parse(datatable.dataset.datatable))
    })
  }
}

export default Datatable
