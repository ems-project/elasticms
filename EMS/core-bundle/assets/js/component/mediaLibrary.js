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
    #activePath;

    #divUploads;
    #listFiles;
    #listFolders;
    #listUploads;
    #listBreadcrumb;

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
        this.#listBreadcrumb = el.querySelector('ul.media-lib-list-breadcrumb');
        this._init();
    }

    _init() {
        this._addEventListeners();
        this._getFolders();
        this._getFiles();
    }

    _addEventListeners() {
        this.#el.querySelectorAll('button.btn-add-folder').forEach(button => {
            button.onclick = () => this._addFolder();
        });
        this.#el.querySelector('.file-uploader-input').onchange = (event) => {
            Array.from(event.target.files).forEach((file) => this._addFile(file));
            event.target.value = "";
        };
        this.#el.querySelector('button.btn-home').onclick = () => this._openFolder();
        this.#el.onclick = (event) => {
            let target = event.target;
            if (target.classList.contains('media-lib-link-folder')) {
                this._openFolder(target.dataset.path, target);
            }
        }
    }

    _openFolder(path, clickedButton)
    {
        this.#listFolders.querySelectorAll('button').forEach((li) => li.classList.remove('active'))
        let button = document.querySelector(`button[data-path="${path}"]`);
        if (button) { button.classList.add('active'); }

        if (clickedButton) {
            let parentLi = clickedButton.parentNode;
            if (parentLi && parentLi.classList.contains('media-lib-folder-children')) {
                parentLi.classList.toggle('open');
            }
        }

        this.#activePath = path;
        this._getFiles(path);
    }

    _openPath(path)
    {
        let currentPath = '';
        path.split('/').filter(f => f !== '').forEach((folderName) => {
            currentPath += `/${folderName}`;

            let parentButton = document.querySelector(`button[data-path="${currentPath}"]`);
            let parentLi = parentButton ? parentButton.parentNode : null;

            if (parentLi && parentLi.classList.contains('media-lib-folder-children')) {
                parentLi.classList.add('open');
            }
        });

        if ('' !== currentPath) {
            let button = document.querySelector(`button[data-path="${currentPath}"]`);
            if (button) button.classList.add('active');
        }
    }

    _addFile(file) {
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

                ajaxJsonPost(mediaLib._makeUrl('add-file/'+fileHash), JSON.stringify({
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

    _addFolder() {
        ajaxModal.load({ url: this._makeUrl('add-folder'), size: 'sm'}, (json) => {
            if (json.hasOwnProperty('path')) {
                this._getFolders(json.path);
            }
        });
    }

    _getFiles(path) {
        this.#listFiles.innerHTML = '';
        this._makeBreadcrumb(path);
        ajaxJsonGet(this._makeUrl('files'), (files) => { this._makeFileItems(files, this.#listFiles) });
    }

    _getFolders(openPath) {
        this.#listFolders.innerHTML = '';
        ajaxJsonGet([this.#urlMediaLib, this.#hash, 'folders'].join('/'), (folders) => {
            this._makeFolderItems(folders, this.#listFolders);
            if (openPath) { this._openPath(openPath); }
        });
    }

    _makeBreadcrumb(path)
    {
        this.#listBreadcrumb.style.display = 'flex';
        this.#listBreadcrumb.innerHTML = '';
        path = ''.concat('/home', path || '');
        let currentPath = '';

        path.split('/').filter(f => f !== '').forEach((folderName) => {
            if (folderName !== 'home') {
                currentPath = currentPath.concat('/', folderName);
            }

            let item = document.createElement('li');
            item.appendChild(this._makeFolderButton(folderName, currentPath));

            this.#listBreadcrumb.appendChild(item);
        });
    }

    _makeFileItems(files, list)
    {
        if (files.length > 0) {
            let liHeading = document.createElement("li");
            ['Name', 'Type', 'Size'].forEach(fileProperty => {
                let divProperty = document.createElement("div");
                divProperty.textContent = fileProperty;
                liHeading.appendChild(divProperty);
            });
            list.appendChild(liHeading);
        }

        files.forEach((file) => {
            let nameLink = document.createElement('a');
            nameLink.download = file['file']['name'];
            nameLink.href = this.#urlViewFile
                .replace(/__file_identifier__/g, file['file']['hash'])
                .replace(/__file_name__/g, file['file']['name']);
            nameLink.textContent = file['file']['name'];

            let divName = document.createElement("div");
            divName.appendChild(nameLink);

            let divType = document.createElement("div");
            divType.textContent = file['file']['type'];

            let divSize = document.createElement("div");
            divSize.textContent = file['file']['size'];

            let liFile = document.createElement("li");
            liFile.append(divName, divType, divSize);

            list.appendChild(liFile);
        });
    }

    _makeFolderItems(folders, list) {
        folders.forEach((folder) => {
            let liFolder = document.createElement("li");
            liFolder.appendChild(this._makeFolderButton(folder['name'], folder['path']));

            if (folder.hasOwnProperty('children')) {
                let ulChildren = document.createElement('ul');
                this._makeFolderItems(folder.children, ulChildren);
                liFolder.appendChild(ulChildren);
                liFolder.classList.add('media-lib-folder-children');
            }

            list.appendChild(liFolder);
        });
    }

    _makeFolderButton(name, path) {
        let button = document.createElement("button");
        button.textContent = name;
        button.dataset.path = path;
        button.classList.add('media-lib-link-folder');

        return button;
    }

    _makeUrl(action) {
        let url = [this.#urlMediaLib, this.#hash, action].join('/');
        if (this.#activePath) {
            url += '?' + new URLSearchParams({path: this.#activePath}).toString();
        }
        return url;
    }
}