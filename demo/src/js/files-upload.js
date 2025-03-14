const dataTransferByFields = []
const msg =
    {
        "fileRemove": {
            "fr": "Supprimer",
            "nl": "Verwijderen",
            "de": "Löschen",
            "en": "Remove"
        },
        "filesSelectedCount": {
            "fr": "%count% fichiers selectionnés",
            "nl": "%count% geselecteerde bestanden",
            "de": "%count% ausgewählte Dateien",
            "en": "%count% files selected"
        },
        "fileSelectedCount": {
            "fr": "1 fichier selectionné",
            "nl": "1 geselecteerde bestand",
            "de": "1 ausgewählte Datei",
            "en": "1 file selected"
        },
        "requiredLabel": {
            "fr": "(*) Champs obligatoires",
            "nl": "(*) Verplichte velden",
            "de": "(*) Benötigte Felder",
            "en": "(*) Required fields"
        },
        "max_multiple_file_size": {
            "fr": "Les fichiers sont trop volumineux. La taille maximale autorisée est de %fileSize%.",
            "nl": "De bestanden zijn te groot. Toegestane maximum grootte is %fileSize%.",
            "de": "Die Dateien sind zu groß. Die maximal zulässige Größe beträgt %fileSize%.",
            "en": "The data is large. The maximum size is %fileSize%."
        },
        "wrongFormat": {
            "fr": "<div class=\"invalid-feedback\" style=\"margin-top: 4px\"><small class=\"d-block\" style=\"display: block\"><span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Erreur</span><span class=\"form-error-message text-danger\">Format incorrect.</span></small></div>",
            "nl": "<div class=\"invalid-feedback\" style=\"margin-top: 4px\"><small class=\"d-block\" style=\"display: block\"><span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Fout</span><span class=\"form-error-message text-danger\">Onjuist formaat.</span></small></div>",
            "de": "<div class=\"invalid-feedback\" style=\"margin-top: 4px\"><small class=\"d-block\" style=\"display: block\"><span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Fehler</span><span class=\"form-error-message text-danger\">Falsches Format.</span></small></div>",
            "en": "<div class=\"invalid-feedback\" style=\"margin-top: 4px\"><small class=\"d-block\" style=\"display: block\"><span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Error</span><span class=\"form-error-message text-danger\">Incorrect format.</span></small></div>",
        },
        "wrongFormatFile": {
            "fr": "<span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Erreur</span><span class=\"form-error-message text-danger\">Format incorrect.</span>&nbsp;",
            "nl": "<span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Fout</span><span class=\"form-error-message text-danger\">Onjuist formaat.</span>&nbsp;",
            "de": "<span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Fehler</span><span class=\"form-error-message text-danger\">Falsches Format.</span>&nbsp;",
            "en": "<span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Error</span><span class=\"form-error-message text-danger\">Incorrect format.</span>&nbsp;",
        },
        "wrongFormatStrip": {
            "fr": "Format incorrect",
            "nl": "Onjuist formaat",
            "de": "Falsches Format",
            "en": "Incorrect format",
        },
        "mandatory": {
            "fr": "<div class=\"invalid-feedback\" style=\"margin-top: 4px\"><small class=\"d-block\" style=\"display: block\"><span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Erreur</span><span class=\"form-error-message text-danger\">Ce champ ne peut pas être vide.</span></small></div>",
            "nl": "<div class=\"invalid-feedback\" style=\"margin-top: 4px\"><small class=\"d-block\" style=\"display: block\"><span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Fout</span><span class=\"form-error-message text-danger\">Deze waarde mag niet leeg zijn.</span></small></div>",
            "de": "<div class=\"invalid-feedback\" style=\"margin-top: 4px\"><small class=\"d-block\" style=\"display: block\"><span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Fehler</span><span class=\"form-error-message text-danger\">Dieser Wert darf nicht leer sein.</span></small></div>",
            "en": "<div class=\"invalid-feedback\" style=\"margin-top: 4px\"><small class=\"d-block\" style=\"display: block\"><span class=\"form-error-icon label label-danger text-uppercase\" style=\"margin-right: 8px\">Error</span><span class=\"form-error-message text-danger\">This field can't be empty.</span></small></div>",
        },
        "successMsg": {
            "fr": "<p><strong>Nous avons bien reçu votre question</strong></p><p>Nous vous tiendrons au courant via e-mail.</p>",
            "nl": "<p><strong>Wij hebben uw vraag goed ontvangen</strong></p><p>We houden je op de hoogte via e-mail.</p>",
            "de": "<p><strong>Wir haben Ihre Frage erhalten</strong></p><p>Wir halten Sie auf dem Laufenden über E-Mail.</p>",
            "en": "<p><strong>We have received your question</strong></p><p>We will keep you informed through e-mail.</p>"
        },
        "errorMsg": {
            "fr": "<p><strong>Erreur d’envoi du formulaire</strong></p><p>Le formulaire n’a pas pu être envoyé. Veuillez réessayer plus tard.</p>",
            "nl": "<p><strong>Verzendfout</strong></p><p>Helaas kon het formulier niet verzonden worden. Probeer het later nog eens. </p>",
            "de": "<p><strong>Versandfehler</strong></p><p>Leider konnte das Formular nicht abgesendet werden. Bitte versuchen Sie es später noch einmal.</p>",
            "en": "<p><strong>Erreur d’envoi du formulaire</strong></p><p>Le formulaire n’a pas pu être envoyé. Veuillez réessayer plus tard.</p>",
        },
        "i18n": {
            "remove": {
                "fr": "Enlever",
                "nl": "Verwijderen",
                "de": "entfernen"
            },
            "count": {
                "fr": "fichiers",
                "nl": "bestanden",
                "de": "Dateien"
            }
        }
    };


export default class FilesUpload {

    load(target) {
        this.langAttr = document.querySelector('html').getAttribute('lang')
        this.fileField = target
        this.boxFileupload = target.closest(".files-upload")
        this.inputFileMaxAllowedSize = parseInt(target.dataset.maxfilesize)
        this.acceptTypes = target.getAttribute('accept')
        this.addDraggableListener(this)
        this.addChangeInputListener(target, this.boxFileupload)
    }

    addDraggableListener(target) {
        const self = this
        this.boxFileupload.addEventListener("dragover", self.fileDragHover, false);
        this.boxFileupload.addEventListener("dragleave", self.fileDragHover, false);
        this.boxFileupload.addEventListener("drop", function(e) {
            self.fileDragHover(e);
            const files = e.target.files || e.dataTransfer.files;
            self.initFilesUpload(files, this);
        }, false);
    }

    addChangeInputListener(target, context) {
        const self = this
        target.addEventListener("change", function(e) {
            self.initFilesUpload(target.files, context);
        });
    }

    initFilesUpload(uplaodFiles, context) {
        const self = this
        var langAttr = self.langAttr
        var fileField = self.fileField
        let dataTransfer = new DataTransfer();
        if (dataTransferByFields[fileField.id] !== undefined) {
            dataTransfer = dataTransferByFields[fileField.id];
        } else {
            dataTransferByFields[fileField.id] = dataTransfer;
        }

        const filenames = [];
        let filesSize = 0;
        for (let i = 0; uplaodFiles && i < uplaodFiles.length; ++i) {
            if (self.inDataTransfer(dataTransfer, uplaodFiles[i])) {
                continue;
            }
            dataTransfer.items.add(uplaodFiles[i]);
        }
        uplaodFiles = dataTransfer.files;

        for (let i = 0; i < uplaodFiles.length; ++i) {
            filesSize += uplaodFiles.item(i).size
            filenames.push(uplaodFiles.item(i).name.split("\\").pop().replace('%20', ' '));
        }

        if (filesSize > self.inputFileMaxAllowedSize) {
            fileField.setCustomValidity(msg.max_multiple_file_size[langAttr].replace('%fileSize%', this.humanFileSize(self.inputFileMaxAllowedSize, true)));
        } else {
            fileField.setCustomValidity('');
        }
        console.log(filenames)

        var fileList = context.querySelector('.file-list')
        fileList.innerHTML = ''
        for (var i = 0; i < filenames.length; ++i) {
            const li = document.createElement('li')
            var liError = ''
            if (uplaodFiles.item(i).type !== undefined && !this.acceptTypes.toLowerCase().includes(uplaodFiles.item(i).type)) {
                liError = msg.wrongFormatFile[langAttr]
                fileField.setCustomValidity(msg.wrongFormatStrip[langAttr])
            }
            const a = document.createElement('a');
            a.className = 'remove-file'
            a.innerHTML = msg.fileRemove[langAttr]
            a.href = '#'
            a.dataset.fileid = i
            li.innerHTML = liError + filenames[i] + ' (' + this.humanFileSize(uplaodFiles.item(i).size, true) + ')  '
            li.append(a)
            fileList.append(li)
        }

        fileField.files = dataTransfer.files;
        var fileList = context.querySelector('.file-list')
        var removelist = fileList.querySelectorAll('.remove-file')
        for(var z = 0; z < removelist.length; z++) {
            var elem = removelist[z];
            elem.addEventListener("click", function(e) {
                e.preventDefault();
                const itemId = this.dataset.fileid;
                const dataTransfer = dataTransferByFields[fileField.id];
                dataTransfer.items.remove(itemId)
                fileField.files = dataTransfer.files;
                self.initFilesUpload(fileField.files, context);
            });
        }
    }

    inDataTransfer(dataTransfer, file) {
        for (let i = 0; i < dataTransfer.files.length; ++i) {
            if (dataTransfer.files[i].lastModified === file.lastModified && dataTransfer.files[i].size === file.size && dataTransfer.files[i].name === file.name) {
                return true;
            }
        }
        return false;
    }

    fileDragHover(e) {
        e.stopPropagation()
        e.preventDefault()
    }

    humanFileSize = function(bytes, si=false, dp=1) {
        const thresh = si ? 1000 : 1024;

        if (Math.abs(bytes) < thresh) {
            return bytes + ' B';
        }

        const units = si
            ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
            : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        let u = -1;
        const r = 10**dp;

        do {
            bytes /= thresh;
            ++u;
        } while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);

        return bytes.toFixed(dp) + ' ' + units[u];
    }

}