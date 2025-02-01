import {setNissInszValidation} from "./validation/niss";
import {addMaxLengthCounter} from "./validation/maxLengthCounter";
import {setBelgiumPhoneValidation} from "./validation/belgiumPhone";
import {setRepeatedValidation} from "./validation/repeated";
import {preventCopyPaste} from "./validation/copyPaste";
import {setBelgiumCompanyNumberValidation} from "./validation/belgiumCompanyNumber";
import {setBelgiumCompanyNumberMultipleValidation} from "./validation/belgiumCompanyNumberMultiple";
import {setBelgiumOnssRszValidation} from "./validation/belgiumOnssRszNumber";
import {setMaxFileSizeValidation} from "./validation/maxFileSize";

export function addValidation(form)
{
    Array.from(form.getElementsByClassName("niss-insz")).forEach(function(item) {
        setNissInszValidation(item);
    });
    Array.from(form.getElementsByClassName("counter")).forEach(function(item) {
        addMaxLengthCounter(item);
    });
    Array.from(form.getElementsByClassName("phone")).forEach(function(item) {
        setBelgiumPhoneValidation(item);
    });
    Array.from(form.getElementsByClassName("repeated")).forEach(function(item) {
        setRepeatedValidation(item);
    });
    Array.from(form.getElementsByClassName("company-number")).forEach(function(item) {
        setBelgiumCompanyNumberValidation(item);
    });
    Array.from(form.getElementsByClassName("company-number-multiple")).forEach(function(item) {
        setBelgiumCompanyNumberMultipleValidation(item);
    });
    Array.from(form.getElementsByClassName("onss-rsz")).forEach(function(item) {
        setBelgiumOnssRszValidation(item);
    });
    Array.from(form.querySelectorAll('[data-maxfilesize]')).forEach(function(item) {
        setMaxFileSizeValidation(item);
    });
}

export function disableCopyPaste(form)
{
    Array.from(form.getElementsByClassName("email-with-confirmation")).forEach(function(item) {
        preventCopyPaste(item);
    })
}

window.formValidation = function (form) {
    addValidation(form);
    disableCopyPaste(form);
};
