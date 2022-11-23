import {i18n} from "../modules/translations";
import {validateBelgiumCompanyNumber} from "./belgiumCompanyNumber.js";

export function setBelgiumCompanyNumberMultipleValidation(element) {
    element.addEventListener('change', function() {
        if(this.value === '') {
            this.setCustomValidity('');
            return;
        }

        if(validateBelgiumCompanyNumberMultiple(this.value)) {
            this.setCustomValidity('');
        } else {
            this.setCustomValidity(i18n.trans('belgium_company_number_multiple', {string: this.value}));
        }
    });

    function validateBelgiumCompanyNumberMultiple(value) {
        let numbers = value.match(/\d+/g);
        
        if (numbers === null) {
            return false;
        }

        let companyNumbers = numbers.map(String).join('');
        if (companyNumbers.length % 10) {
            return false;
        }
        
        var valid = false;
        for (var i = 0; i < companyNumbers.length ; i = i+10)  {
            let  number = companyNumbers.slice(i, i+10);
            if (validateBelgiumCompanyNumber(number)) {
                valid = true;
            } else {
                return false;
            }
        }
        return valid;
    }
}
