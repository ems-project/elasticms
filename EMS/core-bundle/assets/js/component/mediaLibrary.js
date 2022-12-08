import { ajaxJsonGet } from "../helper/ajax";

export default class MediaLibrary {
    #hash;

    constructor (el) {
        this.baseUri = '/component/media-lib';
        this.#hash = el.dataset.hash;
        this.listFiles = el.querySelector("ul.media-lib-files");

        this._getFiles();
    }

    _getFiles() {
        ajaxJsonGet([this.baseUri, this.#hash, 'files'].join('/'), (json, request) => {
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

                this.listFiles.appendChild(liFile);
            }
        });
    }
}