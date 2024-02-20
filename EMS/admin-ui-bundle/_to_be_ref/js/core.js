window.ems_wysiwyg_type_filters = JSON.parse(document.querySelector("BODY").getAttribute('data-wysiwyg-type-filters'));

require('chart.js');
require('fastclick');
require('flot');
require('fullcalendar');
require('inputmask');
require('jquery-knob');
require('jquery-sparkline');
require('moment');
require('pace');
require('raphael');
require('slimscroll');
require('fullcalendar');

//Fix issue CK editor in bootstrap model
//https://ckeditor.com/old/forums/Support/Issue-with-Twitter-Bootstrap#comment-127719
$.fn.modal.Constructor.prototype.enforceFocus = function() {
    const modal_this = this
    $(document).on('focusin.modal', function (e) {
        if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length
            && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select')
            && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
            modal_this.$element.focus()
        }
    })
};

