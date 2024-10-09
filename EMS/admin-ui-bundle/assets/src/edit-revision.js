'use strict'
import $ from 'jquery'
import ajaxRequest from './core/components/ajaxRequest'

import { EMS_CHANGE_EVENT } from './core/events/changeEvent'
import { EMS_CTRL_SAVE_EVENT } from './core/events/ctrlSaveEvent'

let waitingResponse = false
let synch = true

const primaryBox = $('#revision-primary-box')
const updateMode = primaryBox.data('update-mode')

function updateCollectionLabel() {
  $('.collection-panel').each(function () {
    const collectionPanel = $(this)
    const fieldLabel = collectionPanel.data('label-field')
    if (fieldLabel) {
      $(this)
        .children(':first')
        .children(':first')
        .children()
        .each(function () {
          const val = $(this)
            .find('input[name*=' + fieldLabel + ']')
            .val()
          if (typeof val !== 'undefined') {
            $(this)
              .find('.collection-label-field')
              .html(' | ' + val)
          }
        })
    }
  })
}

function updateChoiceFieldTypes() {
  $('.ems-choice-field-type').each(function () {
    const choice = $(this)
    const collectionName = choice.data('linked-collection')
    if (collectionName) {
      $('.collection-panel').each(function () {
        const collectionPanel = $(this)
        if (collectionPanel.data('name') === collectionName) {
          const collectionLabelField = choice.data('collection-label-field')

          collectionPanel
            .children('.panel-body')
            .children('.collection-panel-container')
            .children('.collection-item-panel')
            .each(function () {
              const collectionItem = $(this)
              const index = collectionItem.data('index')
              const id = collectionItem.data('id')
              let label = ' #' + index

              if (collectionLabelField) {
                label += ': ' + $('#' + id + '_' + collectionLabelField).val()
              }

              const multiple = choice.data('multiple')
              const expanded = choice.data('expanded')

              if (expanded) {
                const option = choice.find('input[value="' + index + '"]')
                if (option.length) {
                  const parent = option.closest('.checkbox,.radio')
                  if ($('#' + id + '__ems_internal_deleted').val() === 'deleted') {
                    parent.hide()
                    option.addClass('input-to-hide')
                    if (multiple) {
                      option.attr('checked', false)
                    } else {
                      option.removeAttr('checked')
                    }
                  } else {
                    option.removeClass('input-to-hide')
                    parent.find('.checkbox-radio-label-text').text(label)
                    parent.show()
                  }
                }
              } else {
                const option = choice.find('option[value="' + index + '"]')
                if (option.length) {
                  if ($('#' + id + '__ems_internal_deleted').val() === 'deleted') {
                    option.addClass('input-to-hide')
                  } else {
                    option.removeClass('input-to-hide')
                    option.show()
                    option.text(label)
                  }
                }
              }
            })
        }
      })
    }

    $(this).find('option.input-to-hide').hide()
    $(this)
      .find('.input-to-hide')
      .each(function () {
        $(this).closest('.checkbox,.radio').hide()
      })
  })
}

function createInvalidFeedback(message) {
  const invalidFeedback = document.createElement('DIV')
  invalidFeedback.textContent = message
  invalidFeedback.classList.add('d-block')
  invalidFeedback.classList.add('invalid-feedback')
  return invalidFeedback
}

function onChange(allowAutoPublish = false) {
  if (updateMode === 'disabled') {
    // console.log('No way to save a finalized revision!');
    return
  } else if (updateMode === 'autoPublish' && !allowAutoPublish) {
    // console.log('The auto-save is disabled in auto-publish mode!');
    return
  }

  synch = false

  updateChoiceFieldTypes()
  updateCollectionLabel()

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

  waitingResponse = ajaxRequest
    .post(primaryBox.data('ajax-update'), $('form[name=revision]').serialize())
    .success(function (response) {
      $('.is-invalid').removeClass('is-invalid')
      $('.has-error').removeClass('has-error')
      $('.invalid-feedback').remove()
      $(response.formErrors).each(function (index, item) {
        let target = item.propertyPath
        let targetElement = document.getElementById(`${target}_value`)
        if (targetElement === null) {
          targetElement = document.getElementById(target)
        } else {
          target = `${target}_value`
        }
        if (targetElement !== null) {
          switch (targetElement.nodeName) {
            case 'DIV': {
              const previousElement = targetElement.previousElementSibling
              targetElement.classList.add('has-error')
              if (
                previousElement !== null &&
                previousElement.classList.contains('invalid-feedback') &&
                item.message
              ) {
                $(previousElement).html(item.message)
              } else {
                const invalidFeedback = createInvalidFeedback(item.message)
                targetElement.parentNode.insertBefore(invalidFeedback, targetElement)
              }
              break
            }
            case 'TEXTAREA':
            case 'INPUT': {
              targetElement.classList.add('is-invalid')
              const label = document.querySelector(`label[for=${target}]`)
              if (label !== null) {
                let parent = label.parentElement
                if (parent.classList.contains('input-group')) {
                  parent = parent.parentElement
                }
                const invalidFeedback = label.parentElement.querySelector('.invalid-feedback')
                if (invalidFeedback !== null) {
                  invalidFeedback.textContent = item.message
                } else {
                  const invalidFeedback = createInvalidFeedback(item.message)
                  parent.appendChild(invalidFeedback)
                }
              } else {
                console.log(targetElement)
              }
              break
            }
            default: {
              console.log(targetElement)
              console.log(item)
            }
          }
        } else {
          console.log(item)
        }
      })
    })
    .always(function () {
      waitingResponse = false
      if (!synch) {
        onChange(allowAutoPublish)
      }
    })
}

$('form[name=revision]').submit(function () {
  // disable all pending auto-save
  waitingResponse = true
  synch = true
  $('#data-out-of-sync').remove()
})

window.onload = function () {
  updateChoiceFieldTypes()
  updateCollectionLabel()
  const form = document.querySelector('form[name=revision]')
  form.addEventListener(EMS_CHANGE_EVENT, () => onChange())
  document.addEventListener(EMS_CTRL_SAVE_EVENT, (event) => {
    event.detail.parentEvent.preventDefault()
    onChange(true)
  })
}
