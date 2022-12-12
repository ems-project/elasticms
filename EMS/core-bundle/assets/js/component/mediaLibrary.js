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

                ajaxJsonPost([mediaLib.#urlMediaLib, mediaLib.#hash, 'add-file', fileHash].join('/'), JSON.stringify({
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
        console.debug(this.#uploading);

        let callback = (json) => {
            if (json.hasOwnProperty('success') && json.success === true) {
                //reload folders
            }
        }

        ajaxModal.load({
            url: [this.#urlMediaLib, this.#hash, 'add-folder'].join('/'),
            size: 'sm'
        }, callback);
    }

    _getFiles() {
        ajaxJsonGet([this.#urlMediaLib, this.#hash, 'files'].join('/'), (json) => {
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

    _getFolders() {
        ajaxJsonGet([this.#urlMediaLib, this.#hash, 'folders'].join('/'), (json) => {
            for (let jsonFolderId in json) {
                let jsonFolder = json[jsonFolderId];

                let divFolder = document.createElement("div");
                divFolder.textContent = jsonFolder['name'];

                let liFolder = document.createElement("li");
                liFolder.appendChild(divFolder);

                this.#listFolders.appendChild(liFolder);
            }
        });
    }
}