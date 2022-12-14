import ajaxModal from "./../helper/ajaxModal";
import {ajaxJsonGet, ajaxJsonPost} from "../helper/ajax";
import ProgressBar from "../helper/progressBar";
import FileUploader from "@elasticms/file-uploader";

export default class MediaLibrary {
    #urlMediaLib;
    #urlInitUpload;
    #urlViewFile;

    #el;
    #hash;
    #hashAlgo;
    #uploading = {};
    #activeFolder;

    #divUploads;
    #listFiles;
    #listFolders;
    #listUploads;

    constructor (el, options) {
        this.#urlMediaLib = options.urlMediaLib;
        this.#urlInitUpload = options.urlInitUpload;
        this.#urlViewFile = options.urlFileView;

        this.#el = el;
        this.#hash = el.dataset.hash;
        this.#hashAlgo = options.hashAlgo;

        this.#divUploads = el.querySelector('div.media-lib-uploads');
        this.#listFiles = el.querySelector("ul.media-lib-list-files");
        this.#listFolders = el.querySelector("ul.media-lib-list-folders");
        this.#listUploads = el.querySelector('ul.media-lib-list-uploads');
        this._init();
    }

    _init() {
        this._addEventListeners();
        this._getFolders();
        this._getFiles();
    }

    _addEventListeners() {
        this.#el.querySelectorAll('button.btn-add-folder').forEach(button => {
            button.onclick = () => this._clickAddFolder();
        });
        this.#el.querySelector('.file-uploader-input').onchange = (event) => {
            Array.from(event.target.files).forEach((file) => this._upload(file));
            event.target.value = "";
        };
        this.#el.querySelector('button.btn-home').onclick = () => this._openFolder();
        this.#el.onclick = (event) => {
            let target = event.target;
            if (target.classList.contains('media-lib-link-folder')) {
                this._openFolder(target.dataset.folder, target);
            }
        }
    }

    _openFolder(folder, button)
    {
        this.#listFolders.querySelectorAll('button').forEach((li) => li.classList.remove('active'))
        if (button) {
            let parentLi = button.parentNode;
            if (parentLi.classList.contains('media-lib-folder-children')) {
                parentLi.classList.toggle('open');
            }
            button.classList.add('active');
        }
        this.#activeFolder = folder;
        this._getFiles(folder);
    }

    _upload(file) {
        let id = 'upload-'+ Date.now();
        this.#uploading[id] = 'start';

        let progressBar = new ProgressBar('progress-' + id, {
            'label': file.name
        });

        let fileHash = null;
        let mediaLib = this;
        let liUpload = document.createElement('li');
        liUpload.append(progressBar.element());
        this.#listUploads.appendChild(liUpload);
        this.#divUploads.style.display = 'block';

        new FileUploader({
            file: file,
            algo: this.#hashAlgo,
            initUrl: this.#urlInitUpload,
            onHashAvailable: function(hash, type, name) {
                progressBar.status('Hash available');
                progressBar.progress(0);
                fileHash = hash;
            },
            onProgress: function(status, progress, remaining){
                if (status === 'Computing hash') {
                    progressBar.status('Calculating ...');
                    progressBar.progress(remaining);
                }
                if (status === 'Uploading') {
                    progressBar.status('Uploading: ' + remaining);
                    progressBar.progress(Math.round(progress*100));
                }
            },
            onUploaded: function(){
                progressBar.status('Uploaded');
                progressBar.progress(100);
                progressBar.style('success');

                ajaxJsonPost(mediaLib._url('add-file/'+fileHash), JSON.stringify({
                    'file': {
                        'filename': file.name,
                        'filesize': file.size,
                        'mimetype': file.type
                    }
                }), (json, request) => {
                    if (request.status === 201) {
                        delete mediaLib.#uploading[id];
                        mediaLib.#listUploads.removeChild(liUpload);

                        if (Object.keys(mediaLib.#uploading).length === 0) {
                            mediaLib.#divUploads.style.display = 'none';
                            mediaLib._getFiles();
                        }
                    } else {
                        progressBar.status('Error: ' + message);
                        progressBar.progress(100);
                        progressBar.style('danger');
                    }
                });
            },
            onError: function(message, code){
                progressBar.status('Error: ' + message);
                progressBar.progress(100);
                progressBar.style('danger');
            }
        });
    }

    _clickAddFolder() {
        ajaxModal.load({ url: this._url('add-folder'), size: 'sm'}, (json) => {
            if (json.hasOwnProperty('success') && json.success === true) {
                this._reloadFolders();
            }
        });
    }

    _url(action) {
        let url = [this.#urlMediaLib, this.#hash, action].join('/');
        if (this.#activeFolder) {
            url += '?' + new URLSearchParams({folder: this.#activeFolder}).toString();
        }
        return url;
    }

    _getFiles() {
        this.#listFiles.innerHTML = '';
        ajaxJsonGet(this._url('files'), (json) => {
            if (json.length > 0) {
                let liHeading = document.createElement("li");
                ['Name', 'Type', 'Size'].forEach(fileProperty => {
                    let divProperty = document.createElement("div");
                    divProperty.textContent = fileProperty;
                    liHeading.appendChild(divProperty);
                });
                this.#listFiles.appendChild(liHeading);
            }

            for (let jsonFileId in json) {
                let jsonFile = json[jsonFileId];
                const fileProperties = ['name', 'type', 'size'];

                let liFile = document.createElement("li");

                fileProperties.forEach(fileProperty => {
                    let divProperty = document.createElement("div");

                    if ('name' === fileProperty) {
                        let nameLink = document.createElement('a');
                        nameLink.download = jsonFile['file']['name'];
                        nameLink.href = this.#urlViewFile
                            .replace(/__file_identifier__/g, jsonFile['file']['hash'])
                            .replace(/__file_name__/g, jsonFile['file']['name']);

                        nameLink.textContent = jsonFile['file']['name'];
                        divProperty.appendChild(nameLink);
                    } else {
                        divProperty.textContent = jsonFile['file'][fileProperty];
                    }

                    liFile.appendChild(divProperty);
                });

                this.#listFiles.appendChild(liFile);
            }
        });
    }

    _reloadFolders() {
        this.#listFolders.innerHTML = '';
        setTimeout(() => this._getFolders(), 1000);
    }

    _getFolders() {
        ajaxJsonGet([this.#urlMediaLib, this.#hash, 'folders'].join('/'), (folders) => {
            this._addFolderItems(folders, this.#listFolders);
        });
    }

    _addFolderItems(folders, list) {
        folders.forEach((folder) => {
            let buttonFolder = document.createElement("button");
            buttonFolder.textContent = folder['name'];
            buttonFolder.dataset.folder = folder['path'];
            buttonFolder.classList.add('media-lib-link-folder')

            let liFolder = document.createElement("li");
            liFolder.appendChild(buttonFolder);

            if (folder.hasOwnProperty('children')) {
                let ulChildren = document.createElement('ul');
                this._addFolderItems(folder.children, ulChildren);
                liFolder.appendChild(ulChildren);
                liFolder.classList.add('media-lib-folder-children');
            }

            list.appendChild(liFolder);
        });
    }
}