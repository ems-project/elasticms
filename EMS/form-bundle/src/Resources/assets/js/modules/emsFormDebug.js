import {addDynamicFields, replaceFormFields, addEventListeners} from "../dynamicFields";
import {encoding, security} from '../helpers';

export const DEFAULT_CONFIG = {
    idForm: 'wrapper-form'
};

export class emsFormDebug
{
    constructor(options)
    {
        let config = Object.assign({}, DEFAULT_CONFIG, options);
        this.elementForm = document.getElementById(config.idForm);
        addEventListeners(this.elementForm, this);
    }

    onDynamicFieldChange(data)
    {
        let xhr = new XMLHttpRequest();
        let url = window.location.pathname.replace(/\/debug\/form\//g, '/debug/ajax/');
        xhr.addEventListener("load", evt => emsFormDebug.onResponse(evt, xhr, this));

        xhr.open("POST", url);
        xhr.setRequestHeader("Content-Type",  "application/x-www-form-urlencoded");
        security.addHashCashHeader(data, xhr);
        xhr.send(encoding.urlEncodeData(data));
    }

    onSendConfirmation(data)
    {
        let xhr = new XMLHttpRequest();
        let url = window.location.pathname.replace(/\/debug\/form\//g, '/debug/send-confirmation/');

        xhr.addEventListener("load", evt => emsFormDebug.onResponse(evt, xhr, this));
        xhr.open("POST", url);
        xhr.setRequestHeader("Content-Type",  "application/json");
        xhr.send(data);
    }

    static onResponse(evt, xhr, emsFormInstance)
    {
        if (xhr.status !== 200) {
            return;
        }

        let data = encoding.jsonParse(xhr.responseText);

        if (!data) {
            return;
        }
        
        if (data.instruction === 'dynamic') {
            replaceFormFields(data.response, Object.values(encoding.jsonParse(data.dynamicFields)));
            addDynamicFields(emsFormInstance.elementForm.querySelector('form'), emsFormInstance);
        }
    }
}
