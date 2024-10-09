import $ from 'jquery'
import 'datatables.net'
import 'datatables.net-bs'
import 'datatables.net-bs/css/dataTables.bootstrap.css'
import '../../../css/core/plugins/datatable.scss'
import Core from '../core'

class Datatable {
  load(target) {
    const datatables = target.querySelectorAll('[data-datatable]')
    ;[].forEach.call(datatables, function (element) {
      const datatable = $(element).DataTable(JSON.parse(element.dataset.datatable))
      datatable.on('draw', () => Core.load(element))

      document.querySelectorAll(`[data-datatable-target='${element.id}']`).forEach((btn) =>
        btn.addEventListener('click', () => {
          if (!Object.hasOwn(btn.dataset, 'datatableEvent')) return

          const checked = element.querySelectorAll(`input[name="${element.id}-select[]"]:checked`)

          element.dispatchEvent(
            new CustomEvent(btn.dataset.datatableEvent, {
              detail: {
                selection: Array.from(checked).map((checkbox) => checkbox.value)
              }
            })
          )
        })
      )

      if (!window.dataTables) window.dataTables = []
      window.dataTables[element.id] = datatable
    })
  }
}

export default Datatable
