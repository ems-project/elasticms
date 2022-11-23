import {i18n} from "../modules/translations";
import {BelgiumPhoneNumberValidator} from './belgiumPhoneValidator'

export function setBelgiumPhoneValidation(element)
{
    element.addEventListener('change', function() {
        if(this.value === '') {
            this.setCustomValidity('');
            return;
        }

        let validator = new BelgiumPhoneNumberValidator(this.value);
        if(validator.validate()) {
            this.setCustomValidity('');
        } else {
            this.setCustomValidity(i18n.trans('belgium_phone', {string: this.value}));
        }
    });
}