import {emsForm, defaultCheck} from "./modules/emsForm";

window.emsForm = emsForm;
document.addEventListener('DOMContentLoaded', defaultLoad);

export function defaultLoad() {
    if (defaultCheck()) {
        let form = new emsForm();
        form.init();
    }
}
