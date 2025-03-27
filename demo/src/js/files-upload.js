import t from './translations.js'
const dataTransferByFields = []

export default class FilesUpload {

    load(target) {
        this.langAttr = document.querySelector('html').getAttribute('lang') ?? 'en'
        this.fileField = target
        this.boxFileupload = target.closest('.files-upload')
        this.inputFileMaxAllowedSize = parseInt(target.dataset.maxfilesize)
        this.acceptTypes = target.getAttribute('accept')
        this.addDraggableListener(this)
        this.addChangeInputListener(target, this.boxFileupload)
        this.validationRequired(target)
        this.addClickListener(this.boxFileupload)
    }

    addDraggableListener() {
        const self = this
        this.boxFileupload.addEventListener('dragover', self.fileDragHover, false)
        this.boxFileupload.addEventListener('dragleave', self.fileDragHover, false)
        this.boxFileupload.addEventListener('drop', function(e) {
            self.fileDragHover(e);
            const files = e.target.files || e.dataTransfer.files;
            self.initFilesUpload(files, this);
        }, false)
    }

    addChangeInputListener(target, context) {
        const self = this
        target.addEventListener('change', function() {
            self.initFilesUpload(target.files, context);
        })
    }

    addClickListener(target) {
        const self = this
        const label = self.boxFileupload.querySelector('.custom-file-label')
        self.boxFileupload.querySelector('.files-upload-head').addEventListener('click', (e) => {
            if (e.target.closest('label') !== label) {
                label.click()
            }
        })
    }

    validationRequired(target) {
        const self = this
        target.addEventListener('invalid', function(e) {
            e.preventDefault();
            if (this.required) {
                self.setError(t('required_field'))
            }
        })
    }

    setError(errorMsg) {
        const errorBox = this.boxFileupload.querySelector('.files-upload-error')
        errorBox.innerHTML = errorMsg
        errorBox.classList.remove('d-none')
    }

    removeError() {
        this.boxFileupload.querySelector('.files-upload-error').classList.add('d-none');
    }

    initFilesUpload(uploadFiles, context) {
        const self = this
        const langAttr = self.langAttr
        let fileField = self.fileField
        let dataTransfer = new DataTransfer();
        if (dataTransferByFields[fileField.id] !== undefined) {
            dataTransfer = dataTransferByFields[fileField.id];
        } else {
            dataTransferByFields[fileField.id] = dataTransfer;
        }

        const filenames = [];
        let filesSize = 0;
        for (let i = 0; uploadFiles && i < uploadFiles.length; ++i) {
            if (self.inDataTransfer(dataTransfer, uploadFiles[i])) {
                continue;
            }
            dataTransfer.items.add(uploadFiles[i]);
        }
        uploadFiles = dataTransfer.files;

        for (let i = 0; i < uploadFiles.length; ++i) {
            filesSize += uploadFiles.item(i).size
            filenames.push(uploadFiles.item(i).name.split("\\").pop().replace('%20', ' '));
        }

        if (filesSize > self.inputFileMaxAllowedSize) {
            fileField.setCustomValidity(t('max_size_reached').replace('%fileSize%', this.humanFileSize(self.inputFileMaxAllowedSize, true)))
            self.setError(t('max_size_reached').replace('%fileSize%', this.humanFileSize(self.inputFileMaxAllowedSize, true)))
        } else {
            fileField.setCustomValidity('');
            self.removeError();
        }

        fileField.files = dataTransfer.files;
        let fileList = context.querySelector('.file-list')
        fileList.innerHTML = ''
        let clearAllTag = document.createElement('a')
        clearAllTag.className = 'remove-all-files'
        clearAllTag.innerHTML = t('remove_all')
        clearAllTag.href = '#'
        if(filenames.length === 0) {
           if (self.boxFileupload.querySelector('p.count-file') !== null ) {
               self.boxFileupload.querySelector('p.count-file').remove()
           }
           if (self.boxFileupload.querySelector('.remove-all-files') !== null ) {
                self.boxFileupload.querySelector('.remove-all-files').remove()
           }
        }
        else {
            let p = document.createElement('p')
            if (self.boxFileupload.querySelector('p.count-file') !== null ) {
                p = self.boxFileupload.querySelector('p.count-file')
            } else {
                p.className = 'count-file'
                self.boxFileupload.insertBefore(p, fileList)
                p = self.boxFileupload.querySelector('p.count-file')
            }

            if (filenames.length === 1) {
                p.innerHTML = t('file_selected')
                if (self.boxFileupload.querySelector('.remove-all-files') !== null ) {
                    self.boxFileupload.querySelector('.remove-all-files').remove()
                }
            } else {
                p.innerHTML = t('files_selected').replace('%count%', filenames.length)
                if (self.boxFileupload.querySelector('.remove-all-files') == null ) {
                    self.boxFileupload.append(clearAllTag)
                }
                clearAllTag.addEventListener('click', function(e) {
                    e.preventDefault();
                    const dataTransfer = dataTransferByFields[fileField.id]
                    dataTransfer.items.clear()
                    fileField.files = dataTransfer.files
                    self.initFilesUpload(fileField.files, context)
                });
            }

            for (let i = 0; i < filenames.length; ++i) {
                const li = document.createElement('li')
                if (uploadFiles.item(i).type !== undefined && self.acceptTypes && !self.acceptTypes.toLowerCase().includes(uploadFiles.item(i).type)) {
                    const span = document.createElement('span')
                    span.className = 'form-error-message'
                    span.innerHTML = t('format_not_supported')
                    li.prepend(span)
                    fileField.setCustomValidity(t('incorrect_format'))
                }
                const a = document.createElement('a')
                a.className = 'remove-file'
                a.innerHTML = t('remove')
                a.href = '#'
                a.dataset.fileid = i
                li.innerHTML = li.innerHTML + filenames[i] + ' (' + self.humanFileSize(uploadFiles.item(i).size, true) + ')  '
                li.append(a)
                fileList.append(li)
            }

            const removelist = fileList.querySelectorAll('.remove-file')
            for(let z = 0; z < removelist.length; z++) {
                const elem = removelist[z]
                elem.addEventListener('click', function(e) {
                    e.preventDefault()
                    const itemId = this.dataset.fileid
                    const dataTransfer = dataTransferByFields[fileField.id]
                    dataTransfer.items.remove(itemId)
                    fileField.files = dataTransfer.files
                    self.initFilesUpload(fileField.files, context)
                });
            }
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
