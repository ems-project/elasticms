import ajaxModal from "./../helper/ajaxModal";
import { ajaxJsonGet } from "../helper/ajax";

export default class MediaLibrary {
    #ajaxUrlPath
    #hash;
    #el;
    #listFiles;
    #listFolders;

    constructor (el, options) {
        this.#ajaxUrlPath = options.ajaxUrlPath;
        this.#el = el;
        this.#hash = el.dataset.hash;
        this.#listFiles = el.querySelector("ul.media-lib-list-files");
        this.#listFolders = el.querySelector("ul.media-lib-list-folders");
        this._init();
    }

    _init() {
        this._addEventListeners();
        this._getFolders();
        this._getFiles();
    }

    _addEventListeners() {
        this.#el.querySelectorAll('button.btn-add-folder').forEach(button => {
            button.addEventListener('click', (event) => this._clickAddFolder(event))
        });
    }

    _clickAddFolder(event) {
        event.preventDefault();

        let callback = (json) => {
            if (json.hasOwnProperty('success') && json.success === true) {
                //reload folders
            }
        }

        ajaxModal.load({
            url: [this.#ajaxUrlPath, this.#hash, 'add-folder'].join('/'),
            size: 'sm'
        }, callback);
    }

    _getFiles() {
        ajaxJsonGet([this.#ajaxUrlPath, this.#hash, 'files'].join('/'), (json) => {
            for (let jsonFileId in json) {
                let jsonFile = json[jsonFileId];
                const fileProperties = ['filename', 'filesize', 'mimetype'];

                let divFile = document.createElement("div");
                fileProperties.forEach(fileProperty => {
                    let divProperty = document.createElement("div");
                    divProperty.textContent = jsonFile['file'][fileProperty];
                    divFile.appendChild(divProperty);
                });

                let liFile = document.createElement("li");
                liFile.appendChild(divFile);

                this.#listFiles.appendChild(liFile);
            }
        });
    }

    _getFolders() {
        ajaxJsonGet([this.#ajaxUrlPath, this.#hash, 'folders'].join('/'), (json) => {
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