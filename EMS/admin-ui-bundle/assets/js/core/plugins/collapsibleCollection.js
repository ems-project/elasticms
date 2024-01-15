import $ from 'jquery'

export default class CollapsibleCollection {
  load (target) {
    $(target).find('.collapsible-collection')
      .on('click', '.button-collapse', function () {
        const $isExpanded = ($(this).attr('aria-expanded') === 'true')
        $(this).parent().find('button').attr('aria-expanded', !$isExpanded)

        const panel = $(this).closest('.panel')
        panel.find('.collapse').first().collapse('toggle')
      })
      .on('click', '.button-collapse-all', function () {
        const $isExpanded = ($(this).attr('aria-expanded') === 'true')
        $(this).parent().find('button').attr('aria-expanded', !$isExpanded)

        const panel = $(this).closest('.panel')
        panel.find('.button-collapse').attr('aria-expanded', !$isExpanded)
        panel.find('.button-collapse-all').attr('aria-expanded', !$isExpanded)

        if (!$isExpanded) {
          panel.find('.collapse').collapse('show')
        } else {
          panel.find('.collapse').collapse('hide')
        }
      })
  }
}
