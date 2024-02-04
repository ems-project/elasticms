'use strict'
import $ from 'jquery'

$(window).ready(function () {
  const $listTranslations = $('#i18n_content')
  $listTranslations.data('index', $('#i18n_content > div').length)

  $('.btn-add').on('click', function (e) {
    e.preventDefault()

    const prototype = $listTranslations.data('prototype')
    const index = $listTranslations.data('index')
    const newForm = $(prototype.replace(/__name__/g, index))

    $listTranslations.data('index', index + 1)

    newForm.find('.btn-remove').on('click', function (e) {
      e.preventDefault()

      $(this).parents('.filter-container').remove()
    })

    $listTranslations.append(newForm)
  })

  $('.btn-remove').on('click', function (e) {
    e.preventDefault()

    $(this).parents('.filter-container').remove()
  })
})
