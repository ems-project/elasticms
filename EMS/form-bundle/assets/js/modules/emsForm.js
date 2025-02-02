import {addValidation, disableCopyPaste} from "../validation";
import {addDynamicFields, replaceFormFields, addEventListeners} from "../dynamicFields";
import {encoding, form, security} from '../helpers';
import 'url-polyfill';
import 'formdata-polyfill'

export const DEFAULT_CONFIG = {
    idIframe: 'ems-form-iframe',
    idForm: 'ems-form',
    idMessage: 'ems-message',
    defaultData: null,
    context: null,
    ouuid: false,
    onLoad: function(){ console.log('ems-form loaded'); },
    onSubmit: function(){ console.log('ems-form submit') },
    onResponse: function(response){ console.log( 'ems-form response: ', response.toString()) },
    onConfirmationResponse: function(response){
        console.log( 'ems-form confirmation: ', response);
    },
    onError: function(message){ console.log( 'ems-form error: ' + message) }
};

export function defaultCheck() {
    let elementIframe = document.getElementById(DEFAULT_CONFIG.idIframe);
    let elementForm = document.getElementById(DEFAULT_CONFIG.idForm);
    let elementMessage = document.getElementById(DEFAULT_CONFIG.idMessage);

    return null !== elementIframe && null !== elementForm && null !== elementMessage;
}

export class emsForm
{
    constructor(options)
    {
        let config = Object.assign({}, DEFAULT_CONFIG, options);
        this.elementIframe = document.getElementById(config.idIframe);
        this.elementForm = document.getElementById(config.idForm);
        this.elementMessage = document.getElementById(config.idMessage);
        this.onLoad = config.onLoad;
        this.onSubmit = config.onSubmit;
        this.onError = config.onError;
        this.onResponse = config.onResponse;
        this.defaultData = config.defaultData;
        this.context = config.context;
        this.onConfirmationResponse = config.onConfirmationResponse;
        this.ouuid = config.ouuid;
        this.initialized = false;

        if (this.elementIframe !== null) {
            const url = new URL(this.elementIframe.getAttribute('src'));
            this.origin = url.origin;

            window.addEventListener( 'message', evt => this.onMessage(evt) );
        }
    }

    isValid()
    {
        return this.elementIframe !== null && this.elementForm !== null && this.elementMessage !== null;
    }

    init()
    {
        const form = this;
        form.elementIframe.onload = function() {
            form.getForm();
        };
        form.getForm();
    }

    getForm()
    {
        if (this.isValid() && !this.initialized) {
            this.postMessage({'instruction': 'form', 'form': this.defaultData});
        }
    }

    insertForm(response)
    {
        let parser = new DOMParser;
        let dom = parser.parseFromString('<!doctype html><body>' + response, 'text/html');

        this.elementForm.innerHTML = dom.body.innerHTML;

        let form = this.elementForm.querySelector('form');
        form.addEventListener('submit', evt => this.onSubmitForm(evt));

        addValidation(form);
        disableCopyPaste(form);
        addDynamicFields(form, this);
        addEventListeners(form, this);
        if (typeof this.onLoad === 'function') {
            this.onLoad();
        }
    }

    onMessage(e)
    {
        if (e.origin !== this.origin) {
            return;
        }

        if (typeof e.data !== 'string') {
            //Probably a message from a browser plugin
            return;
        }

        let data = encoding.jsonParse(e.data);


        if (this.ouuid && data.ouuid !== this.ouuid) {
            return;
        }

        if (!data) {
            if (typeof this.onError === 'function') {
                this.onError('JSON parse error or missing data');
            }
            return;
        }
        
        if (data.instruction === undefined) {
            return;
        }
        
        switch (data.instruction) {
            case 'form':
            case 'validation-error':
                this.initialized = true;
                this.insertForm(data.response);
                this.difficulty = parseInt(data.difficulty);
                break;
            case 'submitted':
                this.insertSummaries(data.summaries);
                if (typeof this.onResponse === 'function') {
                    this.onResponse(data.response);
                }
                break;
            case 'dynamic':
                replaceFormFields(data.response, Object.values(encoding.jsonParse(data.dynamicFields)));
                addDynamicFields(this.elementForm.querySelector('form'), this);
                break;
            case 'send-confirmation':
                if (typeof this.onConfirmationResponse === 'function') {
                    this.onConfirmationResponse(data);
                }
                break;
            default:
                if (typeof this.onError === 'function') {
                    this.onError('Unknown data.instruction : ' + data.instruction);
                }
                return;
        }
    }

    insertSummaries(summaries)
    {
        const listContainer = document.createElement('div');
        listContainer.setAttribute('class', 'emsform-summaries');
        const listElement = document.createElement('ul');

        for (let i = 0; i < summaries.length; ++i) {
            const listItem = document.createElement('li');
            listItem.setAttribute('class', 'emsform-summary-'+(summaries[i].status));
            listItem.innerHTML = summaries[i].data;
            listElement.appendChild(listItem);
        }

        listContainer.appendChild(listElement);
        this.elementMessage.appendChild(listContainer);
    }

    onSubmitForm(e)
    {
        e.preventDefault();

        form.disablingSubmitButton(e.target);

        let data = form.getObjectFromFormData(e.target);

        let msg = {
            'instruction': 'submit',
            'form': data,
            'token': security.createToken(data['form[_token]'], this.difficulty)
        };

        if (typeof this.onSubmit === 'function') {
            this.onSubmit();
        }
        this.postMessage(msg);
    }

    onSendConfirmation(data, token)
    {
        this.postMessage({
            'instruction': 'send-confirmation',
            'data': data,
            'token': security.createToken(token, this.difficulty)
        });
    }

    onDynamicFieldChange(data)
    {
        let msg = {
            'instruction': 'dynamic',
            'data': data
        };

        this.postMessage(msg);
    }

    postMessage(msg)
    {
        try {
            this.elementIframe.contentWindow.postMessage(msg, this.origin);
        }
        catch (e) {
            if (typeof this.onError === 'function') {
                this.onError('Post message exception : ' + e.toString());
            }
        }
    }
}
