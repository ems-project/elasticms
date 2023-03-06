import dynForm from "./dyn-forms";


export default function form() {
    const iframes = document.querySelectorAll('iframe[data-form-id]');
    for (let i = 0; i < iframes.length; i++) {
        const form = new skeletonForm(iframes[i]);
        form.loadForm(iframes[i]);
    }

    console.log(iframes.length + ' forms have been initiated');
}

export class skeletonForm
{
    constructor(iframe)
    {
        this.iframe = iframe;
        this.translations = JSON.parse(document.body.getAttribute('data-translations'));
    }

    loadForm(iframe) {
        const self = this;
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (iframeDoc.readyState  !== 'complete'){
            iframe.onload = function() {
                self.loadForm(iframe);
            }
            return;
        }
        const formId = iframe.getAttribute('data-form-id');
        const messageId = iframe.getAttribute('data-message-id');

        const emsForm = new window.emsForm({
            'idForm': formId,
            'idMessage': messageId,
            'idIframe': iframe.id,
            'context': self,
            'onLoad': function() {
                self.onLoad(this.elementForm, this.elementMessage);
            },
            'onSubmit': function() {
                self.onSubmit(this.elementForm, this.elementMessage);
            },
            'onError': function(errorMessage) {
                self.onError(this.elementForm, this.elementMessage, errorMessage);
            },
            'onResponse': function(json) {
                self.onResponse(this.elementForm, this.elementMessage, json);
            }
        });
        emsForm.init();
    }

    onLoad(elementForm, elementMessage) {
        dynForm(elementForm.id);
        const self = this;
        const fileFields = elementForm.querySelectorAll('input[type=file]');
        for (let i = 0; i < fileFields.length; i++) {
            fileFields[i].onchange = function() {
                self.updateFileField(this);
            }
        }
        let $firstInvalid = $('.is-invalid').first();
        if ($firstInvalid.length > 0) {
            this.focus_on_invalid($firstInvalid);
        }
        console.log('My onload function');
    }

    onSubmit(elementForm, elementMessage) {
        const inputs = elementForm.querySelectorAll('input,button,textarea,select');
        for (let i = 0; i < inputs.length; i++) {
            inputs[i].setAttribute('disabled',true)
        }
    }

    onError(elementForm, elementMessage, errorMessage) {
        this.addErrorMessage(elementMessage, this.translations.form_error_try_later);
    }

    onResponse(elementForm, elementMessage, json) {
        const responses = JSON.parse(json);
        let displayedMessage = false;
        for (var i = 0; i < responses.length; ++i) {
            const response = JSON.parse(responses[i]);
            if ('error' === response.status) {
                this.addErrorMessage(elementMessage, this.translations.form_error.replace('%message%', response.data));
                return;
            } else if (response.uid !== undefined) {
                this.addSuccessMessage(elementMessage, this.translations.form_saved.replace('%uid%', response.uid));
                displayedMessage = true;
            }
        }
        if (!displayedMessage) {
            this.addSuccessMessage(elementMessage, this.translations.form_processed);
        }
    }

    updateFileField(fileField) {
        const filenames = [];
        for (var i = 0; i < fileField.files.length; ++i) {
            filenames.push(fileField.files.item(i).name.split("\\").pop().replace('%20', ' '));
        }

        const fileLabel = fileField.parentNode.querySelector('.custom-file-label');
        const fileList = fileField.parentNode.parentNode.querySelector('.file-list');
        console.log(fileList);
        if(filenames.length === 0) {
            fileLabel.classList.remove('selected');
            fileLabel.innerHTML = '';
            fileList.innerHTML = '';
        }
        else if (filenames.length === 1) {
            fileLabel.classList.add('selected');
            fileLabel.innerHTML = filenames.pop();
            fileList.innerHTML = '';
        }
        else {
            fileLabel.classList.add('selected');
            fileLabel.innerHTML = this.translations.file_selected.replace('%count%', filenames.length);
            for (var i = 0; i < filenames.length; ++i) {
                const li = document.createElement('li');
                li.innerHTML = filenames[i];
                fileList.appendChild(li);
            }
        }
    }

    addSuccessMessage(elementMessage, message) {
        const div = document.createElement('div');
        div.classList.add('p-3');
        div.classList.add('mb-2');
        div.classList.add('alert');
        div.classList.add('alert-success');
        div.innerHTML = message;
        elementMessage.appendChild(div);
    }

    addErrorMessage(elementMessage, message) {
        const div = document.createElement('div');
        div.classList.add('p-3');
        div.classList.add('mb-2');
        div.classList.add('alert');
        div.classList.add('alert-warning');
        div.innerHTML = message;
        elementMessage.appendChild(div);
    }

    focus_on_invalid(input) {
        const group = input.closest('.form-group');
        if (group.offset() === undefined)
            return;
        $('html, body').animate({
            scrollTop: group.offset().top // scroll on form-group
        }, 30, function() {
            input.focus(); // then focus on form-control
        });
    }
}
