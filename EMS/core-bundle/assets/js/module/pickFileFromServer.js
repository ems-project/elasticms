import {pickFileModal} from "../helper/ajaxModal";
import {observeDom} from '../helper/observeDom';
import {resizeImage} from "../helper/resizeImage";

export default class PickFileFromServer {
    constructor(target) {
        const buttons = target.querySelectorAll('button.file-browse-server');
        const self = this;

        [].forEach.call(buttons, function(button) {
            button.addEventListener('click', function(event) {
                self.onClick(button);
            });
        });
    }

    onClick(button) {
        const browserFileUrl = this.getBrowserFileUrl();
        if (browserFileUrl) {
            this.openBrowserFileWindow(browserFileUrl);
            return;
        }

        pickFileModal.load({ url: button.dataset.href, title: button.textContent, size: 'lg' },
            (json, modal) => {

            const addClickCallbacks = function(linkList){
                for (let i = 0; i < linkList.length; i++) {
                    linkList[i].onclick = (event) => {
                        const primaryBox = $('body')
                        const initUpload = primaryBox.data('init-upload')
                        const hashAlgo = primaryBox.data('hash-algo');
                        if (event.target.parentNode === undefined || event.target.parentNode.dataset.json === undefined) {
                            return;
                        }
                        event.preventDefault();
                        const data =  JSON.parse(event.target.parentNode.dataset.json)
                        fetch(data.view_url, {mode: 'cors'})
                            .then(res => res.blob())
                            .then(blob => {
                                blob.name = data.filename
                                return resizeImage(hashAlgo, initUpload, blob)
                            })
                            .then((response) => {
                                if (null === response) {
                                    return
                                }
                                data._image_resized_hash = response.hash
                                data.preview_url = response.url
                            })
                            .catch((errorMessage) => {
                                console.error(errorMessage)
                            })
                            .finally(() => {
                                const row = button.closest('.file-uploader-row');
                                row.dispatchEvent(new CustomEvent('updateAssetData', {detail: data}));
                                pickFileModal.close();
                                observer.disconnect();
                            })
                    };
                }
            }

            const linkList = modal.querySelectorAll('div[data-json] > a');
            addClickCallbacks(linkList);
            const observer = observeDom(modal, function(mutationList) {
                [].forEach.call(mutationList, function(mutation) {
                    if(mutation.addedNodes.length < 1) {
                        return;
                    }
                    [].forEach.call(mutation.addedNodes, function (node) {
                        if (node.nodeType !== Node.ELEMENT_NODE) {
                            return;
                        }

                        if (node.matches('div[data-json] > a')) {
                            addClickCallbacks([node]);
                        }

                        addClickCallbacks(node.querySelectorAll('div[data-json] > a'));
                    });
                });
            });
        });
    }

    getBrowserFileUrl() {
        const wysiwygInfoData = document.querySelector('body').dataset.wysiwygInfo;
        if (!wysiwygInfoData) {
            return null;
        }

        const wysiwygInfo = JSON.parse(wysiwygInfoData);
        if (wysiwygInfo && wysiwygInfo.config && wysiwygInfo.config.emsBrowsers && wysiwygInfo.config.emsBrowsers.browser_file && wysiwygInfo.config.emsBrowsers.browser_file.url) {
            return wysiwygInfo.config.emsBrowsers.browser_file.url;
        }

        return null;
    }

    openBrowserFileWindow(url) {
        const width = Math.min(screen.availWidth, 1200);
        const height = Math.min(screen.availHeight, 800);
        const left = Math.round((screen.availWidth - width) / 2);
        const top = Math.round((screen.availHeight - height) / 2);
        const browserWindow = window.open(url, 'ems_file_browser', [
            'popup=yes',
            `width=${width}`,
            `height=${height}`,
            `left=${left}`,
            `top=${top}`,
            'menubar=no',
            'toolbar=no',
            'location=no',
            'status=no',
            'directories=no',
            'titlebar=no',
            'scrollbars=yes',
            'resizable=yes',
        ].join(','));

        if (browserWindow) {
            browserWindow.focus();
        }
    }
}
