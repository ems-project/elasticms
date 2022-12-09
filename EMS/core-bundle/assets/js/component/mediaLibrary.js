import ajaxModal from "./../helper/ajaxModal";
import { ajaxJsonGet } from "../helper/ajax";

export default class MediaLibrary {
    #baseUri
    #hash;
    #el;
    #listFiles;

    constructor (el) {
        this.#baseUri = '/component/media-lib';
        this.#el = el;
        this.#hash = el.dataset.hash;
        this.#listFiles = el.querySelector("ul.media-lib-files");
        this._addEventListeners();
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
            url: [this.#baseUri, this.#hash, 'add-folder'].join('/'),
            size: 'sm'
        }, callback);
    }

    _getFiles() {
        ajaxJsonGet([this.#baseUri, this.#hash, 'files'].join('/'), (json, request) => {
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
}