export class CKEditorConfig {
    #config

    constructor() {
        let assetPath = document.querySelector("BODY").getAttribute('data-asset-path')
        CKEDITOR.plugins.addExternal('div', assetPath+'bundles/emscore/js/cke-plugins/div/plugin.js', '' )
        CKEDITOR.plugins.addExternal('imagebrowser', assetPath+'bundles/emscore/js/cke-plugins/imagebrowser/plugin.js', '' )
    }
}

function emsBrowsers(config) {
    if (!config.hasOwnProperty('emsBrowsers')) return

    if (config.emsBrowsers.hasOwnProperty('browser_object')) {
        let browserObject = config.emsBrowsers.browser_object;
        CKEDITOR.on('dialogDefinition', function (e) {
            if (e.data.name !== 'link') return
            let infoTab = e.data.definition.getContents('info')
            let localPageOptions = infoTab.get('localPageOptions')
            if (localPageOptions) {
                localPageOptions.children.push({
                    type: 'button',
                    id: 'objectBrowse',
                    hidden: 'true',
                    filebrowser: { action: 'Browse', url: browserObject.url},
                    label: browserObject.label
                });
            }
        }, null, null, 1)
    }

    if (config.emsBrowsers.hasOwnProperty('browser_file')) {
        let browserFile = config.emsBrowsers.browser_file;
        CKEDITOR.on('dialogDefinition', function (e) {
            if (e.data.name !== 'link') return
            let infoTab = e.data.definition.getContents('info')
            let fileBrowseButton = infoTab.get('fileBrowse')
            if (fileBrowseButton) {
                fileBrowseButton.label = browserFile.label
                fileBrowseButton.filebrowser = { action: 'Browse', url: browserFile.url}
            }
        }, null, null, 1)
    }

    if (config.emsBrowsers.hasOwnProperty('browser_image')) {
        let browserImage = config.emsBrowsers.browser_image;
        CKEDITOR.on('dialogDefinition', function (e) {
            if (e.data.name !== 'image2') return
            let infoTab =  e.data.definition.getContents( 'info' )
            let imageBrowseButton = infoTab.get('browse')
            if (imageBrowseButton) {
                imageBrowseButton.label = browserImage.label
                imageBrowseButton.filebrowser = { action: 'Browse', url: browserImage.url }
            }
        }, null, null, 1);
    }
}
