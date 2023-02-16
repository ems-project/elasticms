class CKEditorConfigFactory {
    #config;

    constructor() {
        let assetPath = document.querySelector("BODY").getAttribute('data-asset-path') ;
        CKEDITOR.plugins.addExternal('adv_link', assetPath+'bundles/emscore/js/cke-plugins/adv_link/plugin.js', '' );
        CKEDITOR.plugins.addExternal('div', assetPath+'bundles/emscore/js/cke-plugins/div/plugin.js', '' );
        CKEDITOR.plugins.addExternal('imagebrowser', assetPath+'bundles/emscore/js/cke-plugins/imagebrowser/plugin.js', '' );

        const wysiwygInfo = JSON.parse(document.querySelector('body').dataset.wysiwygInfo);

        if (wysiwygInfo.hasOwnProperty('styles')) {
            const stylesSets = wysiwygInfo.styles;
            for(let i=0; i < stylesSets.length; ++i) {
                CKEDITOR.stylesSet.add(stylesSets[i].name, stylesSets[i].config);
            }
        }

        this.#config = wysiwygInfo.config;
    }

    getConfig() {
        return this.#config;
    }
}

export const CKEditorConfig = new CKEditorConfigFactory().getConfig();