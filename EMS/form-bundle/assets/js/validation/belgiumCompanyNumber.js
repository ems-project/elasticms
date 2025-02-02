import {i18n} from "../modules/translations";

export function setBelgiumCompanyNumberValidation(element) {
    element.addEventListener('change', function() {
        if(this.value === '') {
            this.setCustomValidity('');
            return;
        }

        if(validateBelgiumCompanyNumber(this.value)) {
            this.setCustomValidity('');
        } else {
            this.setCustomValidity(i18n.trans('belgium_company_number', {string: this.value}));
        }
    });
}
export function validateBelgiumCompanyNumber(value) {
        const regex = /[01]\d\d\d\d\d\d\d\d\d/gm;
        let numbers = value.match(/\d+/g);
        
        if (numbers === null) {
            return false;
        }

        let companyNumber = numbers.map(String).join('');
        if (companyNumber.length !== 10) {
            return false;
        }
        
        return regex.test(companyNumber);
    }

