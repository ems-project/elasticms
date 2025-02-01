import {i18n} from "../modules/translations";

export function setNissInszValidation(element) {
    element.addEventListener('change', function() {
        if(this.value === '') {
            this.setCustomValidity('');
            return;
        }

        if(validateNissInsz(this.value)) {
            this.setCustomValidity('');
        } else {
            this.setCustomValidity(i18n.trans('niss_insz', {string: this.value}));
        }
    });

    function validateNissInsz(value) {
        const regex = /(\d\d\d\d\d\d\d\d\d)(\d\d)/gm;
        let numbers = value.match(/\d+/g);
        if (numbers === null) {
            return false;
        }

        let niss = numbers.map(String).join('');
        if (niss.length !== 11) {
            return false;
        }
        
        let m;
        let valid;
        while ((m = regex.exec(niss)) !== null) {
            // This is necessary to avoid infinite loops with zero-width matches
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }

            let base = m[1];
            let control = m[2];
            valid = parseInt(control) === (97 - (base % 97));
            if (!valid) {
                valid = parseInt(control) === (97 - ('2'.concat(base) % 97));
            }
        }
        return valid;
    }
}