import "child-replace-with-polyfill";
import {addDynamicChoiceSelect, clickSendConfirmation} from "./modules/eventListeners";

export function addDynamicFields(form, emsForm) {
    Array.from(form.getElementsByClassName("dynamic-choice-select")).forEach(function(item) {
        addDynamicChoiceSelect(item, emsForm);
    });
}

export function replaceFormFields(response, fieldIds) {
    let parser = new DOMParser;
    let dom = parser.parseFromString('<!doctype html><body>' + response, 'text/html');

    let formElement;
    let newElement;
    Array.prototype.forEach.call(fieldIds, function(fieldId) {
        formElement = document.getElementById(fieldId);
        newElement = dom.getElementById(fieldId);

        if (formElement.tagName === 'SELECT') {
            formElement = formElement.parentElement;
        }
        if (newElement.tagName === 'SELECT') {
            newElement = newElement.parentElement;
        }

        formElement.replaceWith(newElement);
    });
}

export function addEventListeners(form, emsForm) {
    Array.from(form.getElementsByClassName("btn-send-confirmation")).forEach(function (item) {
        clickSendConfirmation(item, emsForm);
    })
}

window.dynamicFields = function (form, emsForm) {
    addDynamicFields(form, emsForm);
};
