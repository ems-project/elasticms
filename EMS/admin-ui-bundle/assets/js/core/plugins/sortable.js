import 'jquery-ui'
import '../librairies/sortable'
import '../librairies/nestedSortable'
import '../../../css/core/plugins/sortable.scss'

class Sortable {
    load(target) {
        const nestedList = $(target).find('.nested-sortable');
        nestedList.each(function() {
            const nestedList = $(this);

            let maxLevels = nestedList.data('nested-max-level');
            let isTree = nestedList.data('nested-is-tree');
            let handle = nestedList.data('nested-handle');

            if(typeof maxLevels === 'undefined') {
                maxLevels = 1;
            }
            else {
                maxLevels = Number(maxLevels);
            }

            if(typeof isTree === 'undefined') {
                isTree = false;
            }
            else {
                isTree = ( isTree === 'true' );
            }

            if(typeof handle === 'undefined') {
                handle = 'div';
            }

            nestedList.nestedSortable({
                forcePlaceholderSize: true,
                handle: handle,
                helper: 'clone',
                items: 'li',
                opacity: .6,
                placeholder: 'placeholder',
                revert: 250,
                tabSize: 25,
                tolerance: 'pointer',
                toleranceElement: '> div',
                maxLevels: maxLevels,
                expression: /()(.+)/,

                isTree: isTree,
                expandOnHover: 700,
                startCollapsed: true
            });
        });

        $(this.target).find('.reorder-button').on('click', function(){
            const form = $(this).closest('form');
            const hierarchy = form.find('.nested-sortable').nestedSortable('toHierarchy', {startDepthCount: 0});
            form.find('input.reorder-items').val(JSON.stringify(hierarchy)).trigger("change");
        });

        let findCollapseButtonPrefix = '.json_menu_editor_fieldtype_widget ';

        if ($(this.target).find(findCollapseButtonPrefix).length === 0) {
            findCollapseButtonPrefix = '.mjs-nestedSortable ';
        }

        if ($(this.target).hasClass('mjs-nestedSortable')) {
            findCollapseButtonPrefix = '';
        }

        $(this.target).find(findCollapseButtonPrefix+'.button-collapse').click(function (event) {
            event.preventDefault();
            const $isExpanded = ($(this).attr('aria-expanded') === 'true');
            $(this).parent().find('> button').attr('aria-expanded', !$isExpanded);
            let $panel = $(this).closest('.collapsible-container');
            if ($isExpanded) {
                $panel.find('ol').first().show();
            }
            else {
                $panel.find('ol').first().hide();
            }
        });

        $(this.target).find(findCollapseButtonPrefix+'.button-collapse-all').click(function (event) {
            event.preventDefault();
            const $isExpanded = ($(this).attr('aria-expanded') === 'true');
            let $panel = $(this).closest('.collapsible-container');
            $panel.find('.button-collapse').attr('aria-expanded', !$isExpanded);
            $panel.find('.button-collapse-all').attr('aria-expanded', !$isExpanded);
            if ($isExpanded) {
                $panel.find('ol').not('.not-collapsible').show();
            }
            else {
                $panel.find('ol').not('.not-collapsible').hide();
            }
        });
    }
}

export default Sortable
