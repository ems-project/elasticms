'use strict';

( function() {
    let openDashboard = function (button) {
        this.popup(button.dataset.url, '80%', '70%', 'location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=yes,alwaysRaised=yes,resizable=yes,scrollbars=yes');
    }

    function attachDashboardBrowser( editor, dashboardsConfig, dialog, definition, elements ) {
        if (!elements || !elements.length)
            return;

        let element;
        for ( let i = elements.length; i--; ) {
            element = elements[i];

            if ( element.type === 'hbox' || element.type === 'vbox' || element.type === 'fieldset' )
                attachDashboardBrowser( editor, dashboardsConfig, dialog, definition, element.children );

            if (element.type !== 'html' ||  !element.dashboardBrowser )
                continue;

            let html = ['<div>'];

            dashboardsConfig.forEach((dashboardConfig) => {
                html.push('<a href="javascript: void(0);" role="button"' +
                    ' class="cke_dialog_ui_button" style="margin-right: 10px;"' +
                    ' onclick="CKEDITOR.tools.callFunction(' + editor._.fnDashboardLoad +', this ); return false;"' +
                    ` data-url="${dashboardConfig.url}" tabindex="-1">` +
                    `<span class="cke_dialog_ui_button">${dashboardConfig.label}</span></a>`
                );
            })

            html.push('</div>');
            element.html = html.join('');
        }
    }

    CKEDITOR.plugins.add( 'dashboard_browser', {
        requires: 'popup',
        init: function( editor ) {
            editor._.fnDashboardLoad = CKEDITOR.tools.addFunction( openDashboard, editor );

            editor.on( 'destroy', function() {
                CKEDITOR.tools.removeFunction( this._.fnDashboardLoad );
            } );
        }
    } );

    CKEDITOR.on( 'dialogDefinition', function( evt ) {
        if ( !evt.editor.plugins.dashboard_browser )
            return;

        let emsConfig = evt.editor.config.hasOwnProperty('ems') ? evt.editor.config.ems : {};

        if (!emsConfig.hasOwnProperty('dashboards')) {
            return;
        }

        let definition = evt.data.definition,
            element;

        for ( let i = 0; i < definition.contents.length; ++i ) {
            if ((element = definition.contents[i])) {
                attachDashboardBrowser(evt.editor, emsConfig.dashboards, evt.data, definition, element.elements)
            }
        }
    });
})();

