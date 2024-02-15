'use strict';

import {editRevisionEventListeners} from "./js/editRevisionEventListeners";

$("form[name=revision]").submit(function( ) {
    //disable all pending auto-save
    waitingResponse = true;
    synch = true;
    $('#data-out-of-sync').remove();
});

function updateCollectionLabel()
{
    $('.collection-panel').each(function() {
        const collectionPanel = $(this);
        const fieldLabel = collectionPanel.data('label-field');
        if (fieldLabel) {
            $(this).children(':first').children(':first').children().each(function(){
                let val = $(this).find('input[name*='+fieldLabel+']').val();
                if (typeof val !== 'undefined') {
                    $(this).find('.collection-label-field').html(' | ' + val)
                }
            });
        }
    });
}

function updateChoiceFieldTypes()
{
    $('.ems-choice-field-type').each(function(){
        const choice = $(this);
        const collectionName = choice.data('linked-collection');
        if(collectionName)
        {

            $('.collection-panel').each(function()
            {
                const collectionPanel = $(this);
                if(collectionPanel.data('name') === collectionName)
                {
                    const collectionLabelField = choice.data('collection-label-field');

                    collectionPanel.children('.panel-body').children('.collection-panel-container').children('.collection-item-panel').each(function(){

                        const collectionItem = $(this);
                        const index = collectionItem.data('index');
                        const id = collectionItem.data('id');
                        let label = ' #'+index;

                        if(collectionLabelField)
                        {
                            label += ': '+$('#'+id+'_'+collectionLabelField).val();
                        }

                        const multiple = choice.data('multiple');
                        const expanded = choice.data('expanded');

                        if(expanded)
                        {
                            const option = choice.find('input[value="'+index+'"]');
                            if(option.length)
                            {
                                const parent = option.closest('.checkbox,.radio');
                                if($('#'+id+'__ems_internal_deleted').val() === 'deleted'){
                                    parent.hide();
                                    option.addClass('input-to-hide');
                                    if(multiple)
                                    {
                                        option.attr('checked', false);
                                    }
                                    else
                                    {
                                        option.removeAttr("checked");
                                    }
                                }
                                else{
                                    option.removeClass('input-to-hide');
                                    parent.find('.checkbox-radio-label-text').text(label);
                                    parent.show();
                                }
                            }
                        }
                        else
                        {
                            const option = choice.find('option[value="'+index+'"]');
                            if(option.length)
                            {
                                if($('#'+id+'__ems_internal_deleted').val() === 'deleted')
                                {
                                    option.addClass('input-to-hide');
                                }
                                else
                                {
                                    option.removeClass('input-to-hide');
                                    option.show();
                                    option.text(label);
                                }

                            }
                        }

                    })
                }

            });

        }

        $(this).find('option.input-to-hide').hide();
        $(this).find('.input-to-hide').each(function(){
            $(this).closest('.checkbox,.radio').hide();
        })
    });
}

$(window).ready(function() {
    updateChoiceFieldTypes();
    updateCollectionLabel();
    editRevisionEventListeners($('form[name=revision]'), onFormChange);
});

if (null !== document.querySelector('form[name="revision"]')) {
    $(document).keydown(function (e) {
        let key = undefined;
        /**
         * @param {{keyIdentifier:string}} e
         */
        const possible = [e.key, e.keyIdentifier, e.keyCode, e.which];

        while (key === undefined && possible.length > 0) {
            key = possible.pop();
        }

        if (typeof key === "number" && (115 === key || 83 === key) && (e.ctrlKey || e.metaKey) && !(e.altKey)) {
            e.preventDefault();
            onFormChange(e, true);
            return false;
        }
        return true;

    });
}

