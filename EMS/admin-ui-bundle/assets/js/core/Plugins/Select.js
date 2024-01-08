import 'bootstrap-select'
import 'select2/dist/js/select2.full'
import 'bootstrap-select/sass/bootstrap-select.scss'
import 'select2/src/scss/core.scss'
import '../../../css/core/plugins/select.scss'

class Select {
    load(target) {
        $(target).find('.selectpicker').selectpicker()
        $(target).find(".select2").select2({
            allowClear: true,
            placeholder: "",
            escapeMarkup: function (markup) { return markup; }
        })
    }
}

export default Select
