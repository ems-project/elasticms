'use strict'
import $ from 'jquery'
import ajaxRequest from './core/components/ajaxRequest'

import { EMS_CHANGE_EVENT } from './core/events/changeEvent'

let waitingResponse = false
let synch = true

const primaryBox = $('#revision-primary-box')
const updateMode = primaryBox.data('update-mode')

function onChange (allowAutoPublish = false) {
  if (updateMode === 'disabled') {
    // console.log('No way to save a finalized revision!');
    return
  } else if (updateMode === 'autoPublish' && !allowAutoPublish) {
    // console.log('The auto-save is disabled in auto-publish mode!');
    return
  }

  synch = false

  // updateChoiceFieldTypes();
  // updateCollectionLabel();

  if (waitingResponse) {
    return
    // abort the request might be an option, but it overloads the server
    // waitingResponse.abort();
  }

  synch = true
  // update ckeditor's text areas
  /* for (let i in CKEDITOR.instances) {
        if(CKEDITOR.instances.hasOwnProperty(i)) {
            CKEDITOR.instances[i].updateElement();
        }
    } */

  waitingResponse = ajaxRequest.post(primaryBox.data('ajax-update'), $('form[name=revision]').serialize())
    .success(function (response) {
      $('.is-invalid').removeClass('is-invalid')
      $('span.help-block').remove()
      $(response.formErrors).each(function (index, item) {
        let target = item.propertyPath
          console.log(target)
        const targetLabel = $('#' + target + '__label')
        const targetError = $('#' + target + '__error')

        const propPath = $('#' + item.propertyPath + '_value')
        if (propPath.length && propPath.prop('nodeName') === 'TEXTAREA') {
          target = item.propertyPath + '_value'
        }

        const targetParent = $('#' + target)
        if (targetLabel.length) {
          targetLabel.closest('div.form-group').addClass('has-error')
          if (item.message && targetError.length > 0) {
            targetError.addClass('has-error')
            if ($('#' + target + '__error span.help-block').length === 0) {
              targetError.append('<span class="help-block"><ul class="list-unstyled"></ul></span>')
            }
            $('#' + target + '__error' + ' span.help-block ul.list-unstyled').append('<li><span class="glyphicon glyphicon-exclamation-sign"></span> ' + item.message + '</li>')
          }
        } else {
          $('#' + target).closest('div.form-group').addClass('has-error')
          targetParent.parents('.form-group').addClass('has-error')
          if (item.message) {
            if (targetParent.parents('.form-group').find(' span.help-block').length === 0) {
              targetParent.parent('.form-group').append('<span class="help-block"><ul class="list-unstyled"><li><span class="glyphicon glyphicon-exclamation-sign"></span> ' + item.message + '</li></ul></span>')
            } else {
              targetParent.parents('.form-group').find(' span.help-block ul.list-unstyled').append('<li><span class="glyphicon glyphicon-exclamation-sign"></span> ' + item.message + '</li>')
            }
          }
        }
      })
    })
    .always(function () {
      waitingResponse = false
      if (!synch) {
        onChange()
      }
    })
}

window.onload = function () {
  const form = document.querySelector('form[name=revision]')
  form.addEventListener(EMS_CHANGE_EVENT, (event) => onChange(form, event.detail.input, event))
}
