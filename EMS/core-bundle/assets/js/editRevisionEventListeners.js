import EmsListeners from "./EmsListeners";

let CKEditorConfig = false;
function initCKEditor() {
    if (false === CKEditorConfig) {
        const assetPath = document.querySelector("BODY").getAttribute('data-asset-path') ;
        CKEDITOR.plugins.addExternal('adv_link', assetPath+'bundles/emscore/js/cke-plugins/adv_link/plugin.js', '' );
        CKEDITOR.plugins.addExternal('div', assetPath+'bundles/emscore/js/cke-plugins/div/plugin.js', '' );
        CKEDITOR.plugins.addExternal('imagebrowser', assetPath+'bundles/emscore/js/cke-plugins/imagebrowser/plugin.js', '' );
        CKEDITOR.plugins.addExternal('dashboard_browser', assetPath+'bundles/emscore/js/cke-plugins/dashboard_browser/plugin.js', '' );

        const wysiwygInfo = JSON.parse(document.querySelector('body').dataset.wysiwygInfo);

        if (wysiwygInfo.hasOwnProperty('styles')) {
            const stylesSets = wysiwygInfo.styles;
            for(let i=0; i < stylesSets.length; ++i) {
                CKEDITOR.stylesSet.add(stylesSets[i].name, stylesSets[i].config);
            }
        }
        CKEditorConfig = wysiwygInfo.config;
    }

    return CKEditorConfig;
}

function editRevisionEventListeners(target, onChangeCallback = null){
    const ckconfig = initCKEditor();
    new EmsListeners(target.get(0), onChangeCallback);

    target.find('.remove-content-button').on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        const panel = $(this).closest('.collection-item-panel');
        panel.find('input._ems_internal_deleted').val('deleted');
        panel.hide();

        if (onChangeCallback) {
            onChangeCallback();
        }
    });

    if (onChangeCallback) {
        target.find("input").not(".ignore-ems-update,.datetime-picker,datetime-picker").on('input', onChangeCallback);
        target.find("select").not(".ignore-ems-update").on('change', onChangeCallback);
        target.find("textarea").not(".ignore-ems-update").on('input', onChangeCallback);
    }

    target.find('.add-content-button').on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        const panel = $(this).closest('.collection-panel');
        const index = panel.data('index');
        const prototype = panel.data('prototype');
        const prototypeName = new RegExp(panel.data('prototype-name'), "g");
        const prototypeLabel = new RegExp(panel.data('prototype-label'), "g");

        // Replace '__label__name__$fieldId__' in the prototype's HTML to
        // Replace '__name__$fieldId__' in the prototype's HTML to
        // instead be a number based on how many items we have
        const newForm = $(prototype.replace(prototypeLabel, (index+1)).replace(prototypeName, index));
        // increase the index with one for the next item
        panel.data('index', (index + 1));

        editRevisionEventListeners(newForm);

        panel.children('.panel-body').children('.collection-panel-container').append(newForm);

        if (onChangeCallback) {
            onChangeCallback();
        }
    });

    target.find('.ems-sortable > div').sortable({
        handle: ".ems-handle"
    });

    target.find('.selectpicker').selectpicker();

    target.find(".ckeditor_ems").each(function(){
        let height = $( this ).attr('data-height');
        if(!height){
            height = 400;
        }

        const format_tags = $( this ).attr('data-format-tags');
        if(format_tags){
            ckconfig.format_tags = format_tags;
        }

        const styles_set = $( this ).attr('data-styles-set');
        if(styles_set){
            ckconfig.stylesSet = styles_set;
        }

        const content_css = $( this ).attr('data-content-css');
        if(content_css){
            ckconfig.contentsCss = content_css;
        }

        const language = $( this ).attr('data-lang');
        if(language){
            ckconfig.language = language;
        }

        ckconfig.referrerEmsId = $( this ).attr('data-referrer-ems-id');

        let tableDefaultCss = $( this ).attr('data-table-default-css');
        if(typeof tableDefaultCss == 'undefined'){
            tableDefaultCss = 'table table-bordered';
        }


        ckconfig.height = height;
        ckconfig.div_wrapTable = 'true';

        //http://stackoverflow.com/questions/18250404/ckeditor-strips-i-tag
        //TODO: see if we could moved it to the wysiwyg templates tools
        ckconfig.allowedContent = true;
        ckconfig.extraAllowedContent = 'p(*)[*]{*};div(*)[*]{*};li(*)[*]{*};ul(*)[*]{*}';
        CKEDITOR.dtd.$removeEmpty.i = 0;

        if (onChangeCallback && !CKEDITOR.instances[$( this ).attr('id')] && $(this).hasClass('ignore-ems-update') === false) {
            CKEDITOR.replace(this, ckconfig).on('key', onChangeCallback );
        }
        else {
            CKEDITOR.replace(this, ckconfig);
        }


        //Set defaults that are compatible with bootstrap for html generated by CKEDITOR (e.g. tables)
        CKEDITOR.on( 'dialogDefinition', function( ev )
        {
            // Take the dialog name and its definition from the event data.
            const dialogName = ev.data.name;
            const dialogDefinition = ev.data.definition;

            // Check if the definition is from the dialog we're interested in (the "Table" dialog).
            if ( dialogName === 'table' )
            {
                // Get a reference to the "Table Info" tab.
                const infoTab = dialogDefinition.getContents( 'info' );

                const txtBorder = infoTab.get( 'txtBorder');
                txtBorder['default'] = 0;
                const txtCellPad = infoTab.get( 'txtCellPad');
                txtCellPad['default'] = "";
                const txtCellSpace = infoTab.get( 'txtCellSpace');
                txtCellSpace['default'] = "";
                const txtWidth = infoTab.get( 'txtWidth' );
                txtWidth['default'] = "";

                // Get a reference to the "Table Advanced" tab.
                const advancedTab = dialogDefinition.getContents( 'advanced' );

                const advCSSClasses = advancedTab.get( 'advCSSClasses' );
                advCSSClasses['default'] = tableDefaultCss;

            }
        });

        if (ckconfig.hasOwnProperty('emsAjaxPaste')) {
            let editor = CKEDITOR.instances[$( this ).attr('id')];
            editor.on('beforePaste', (event) => {
                let pastedText = event.data.dataTransfer.getData('text/html');
                if (!pastedText || pastedText === '') return

                event.cancel();
                fetch(ckconfig.emsAjaxPaste, {
                    method: 'POST',
                    body: JSON.stringify({ content: pastedText }),
                    headers: { 'Content-Type': 'application/json' }
                }).then((response) => {
                    return response.ok ? response.json().then((json) => {
                        event.data.dataValue = json.content;
                        editor.fire( 'paste', event.data);
                    }): Promise.reject(response)
                }).catch(() => { console.error('error pasting') })
            });
        }
    });

    target.find(".colorpicker-component").colorpicker();

    if (onChangeCallback) {
        target.find(".colorpicker-component").bind('changeColor', onChangeCallback);
    }

    target.find(".timepicker").each(function(){

        const settings = {
            showMeridian: 	$( this ).data('show-meridian'),
            explicitMode: 	$( this ).data('explicit-mode'),
            minuteStep: 	$( this ).data('minute-step'),
            disableMousewheel: true,
            defaultTime: false
        };

        $( this ).unbind( "change" );

        if ($(this).not('.ignore-ems-update')) {
            if (onChangeCallback) {
                $( this ).timepicker(settings).on('changeTime.timepicker', onChangeCallback);
            }
        } else {
            $( this ).timepicker(settings);
        }
    });


    target.find('.datepicker').each(function( ) {

        $(this).unbind('change');
        const params = {
            format: $(this).attr('data-date-format'),
            todayBtn: true,
            weekStart: $(this).attr('data-week-start'),
            daysOfWeekHighlighted: $(this).attr('data-days-of-week-highlighted'),
            daysOfWeekDisabled: $(this).attr('data-days-of-week-disabled'),
            todayHighlight: $(this).attr('data-today-highlight')
        };

        if($(this).attr('data-multidate') && $(this).attr('data-multidate') !== 'false'){
            params.multidate = true;
        }

        $(this).datepicker(params);

        if (onChangeCallback) {
            $(this).not(".ignore-ems-update").on('dp.change', onChangeCallback);
        }
    });

    target.find('.datetime-picker').each(function( ) {
        let $element = $(this);
        $element.unbind('change');
        $element.datetimepicker({
            keepInvalid: true, //otherwise daysOfWeekDisabled or disabledHours will not work!
            extraFormats: [moment.ISO_8601]
        });
        if (onChangeCallback) {
            $element.not(".ignore-ems-update").on('dp.change', onChangeCallback);
        }
    });

    target.find('.ems_daterangepicker').each(function( ) {

        const options = $(this).data('display-option');
        $(this).unbind('change');

        if ($(this).not('.ignore-ems-update')) {
            if (onChangeCallback) {
                $(this).daterangepicker(options, function() { onChangeCallback(); });
            }
        } else {
            $(this).daterangepicker(options);
        }
    });
}

export {editRevisionEventListeners};