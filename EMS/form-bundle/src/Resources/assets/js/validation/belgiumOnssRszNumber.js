import {i18n} from "../modules/translations";

export function setBelgiumOnssRszValidation(element) {
    element.addEventListener('change', function() {
        if(this.value === '') {
            this.setCustomValidity('');
            return;
        }

        if(validateBelgiumOnssRsz(this.value)) {
            this.setCustomValidity('');
        } else {
            this.setCustomValidity(i18n.trans('belgium_onss_rsz', {string: this.value}));
        }
    });

    function validateBelgiumOnssRsz(value) {
        let numbers = value.match(/\d+/g);

        if (numbers === null) {
            return false;
        }

        let nsso = numbers.map(String).join('');
        
        if (nsso.length >= 9 && nsso.length <= 10) {
            return true;
        }
        
        return false;
    }
}
