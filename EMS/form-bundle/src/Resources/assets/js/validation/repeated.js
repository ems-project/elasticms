import {i18n} from "../modules/translations";

export function setRepeatedValidation(element) {
    const original = document.getElementById(element.id.replace('_second', '_first'));

    original.addEventListener('change', function(){
        const repetition = document.getElementById(this.id.replace('_first', '_second'));

        if ("createEvent" in document) {
            var evt = document.createEvent("HTMLEvents");
            evt.initEvent("change", false, true);
            repetition.dispatchEvent(evt);
        } else {
            repetition.fireEvent("onchange");
        }
    });

    element.addEventListener('change', function() {
        if(this.value === '') {
            this.setCustomValidity('');
            return;
        }

        if(validateRepetition(this.value, this.id)) {
            this.setCustomValidity('');
        } else {
            this.setCustomValidity(i18n.trans('repeated', {string: getLabel(this.id)}));
        }
    });

    function getLabel(id) {
        return document.getElementById(id + '_label').textContent;
    }

    function validateRepetition(value, id) {
        let original = document.getElementById(id.replace('_second', '_first'));
        return value === original.value;
    }
}
