import { TempusDominus } from '@eonasdan/tempus-dominus'
import '@eonasdan/tempus-dominus/src/scss/tempus-dominus.scss'
import ChangeEvent from '../events/changeEvent'

class Datetime {
  #iframes = []

  load(target) {
    this.loadDatetime(target)
    this.loadDate(target)
    this.loadDateRange(target)
    this.loadTime(target)
  }

  loadTime(target) {
    const options = this.defaultOptions()
    options.display.components.calendar = false
    this.loadPicker(target, '.timepicker', options)
  }

  loadDateRange(target) {
    const options = this.defaultOptions()
    options.dateRange = true
    options.multipleDatesSeparator = ' - '
    this.loadPicker(target, '.ems_daterangepicker', options)
  }

  loadDate(target) {
    const options = this.defaultOptions()
    options.display.components.clock = false
    options.multipleDatesSeparator = ','
    this.loadPicker(target, '.datepicker', options)
  }

  loadDatetime(target) {
    const options = this.defaultOptions()
    this.loadPicker(target, '.datetime-picker', options)
  }

  loadPicker(target, query, options) {
    const pickers = target.querySelectorAll(query)
    for (let i = 0; i < pickers.length; i++) {
      if (pickers[i].dataset.multidate) {
        options.multipleDates = pickers[i].dataset.multidate
      }
      if (pickers[i].dataset.weekStart) {
        options.localization.startOfTheWeek = pickers[i].dataset.weekStart
      }
      if (pickers[i].dataset.dateFormat) {
        options.localization.format = pickers[i].dataset.dateFormat
      }
      if (pickers[i].dataset.daysOfWeekDisabled) {
        options.restrictions.daysOfWeekDisabled = JSON.parse(pickers[i].dataset.daysOfWeekDisabled)
      }
      if (pickers[i].dataset.dateDaysOfWeekDisabled) {
        options.restrictions.daysOfWeekDisabled = JSON.parse(
          pickers[i].dataset.dateDaysOfWeekDisabled
        )
      }
      if (pickers[i].dataset.dateDisabledHours) {
        options.restrictions.disabledHours = JSON.parse(pickers[i].dataset.dateDisabledHours)
      }
      if (undefined !== pickers[i].dataset.showMeridian) {
        let format
        if (pickers[i].dataset.showMeridian === 'true') {
          format = 'h:mm'
          options.localization.hourCycle = 'h12'
        } else {
          format = 'H:mm'
          options.localization.hourCycle = 'h23'
        }
        if (pickers[i].dataset.showSeconds) {
          format += ':ss'
          options.display.components.seconds = true
        }
        if (pickers[i].dataset.showMeridian === 'true') {
          format += ' T'
        }
        options.localization.format = format
      }
      if (pickers[i].dataset.displayOption) {
        const displayOptions = JSON.parse(pickers[i].dataset.displayOption)
        if (undefined !== displayOptions.locale.firstDay) {
          options.localization.startOfTheWeek = displayOptions.locale.firstDay
        }
        if (undefined !== displayOptions.locale.format) {
          options.localization.format = displayOptions.locale.format
        }
        if (undefined !== displayOptions.timePicker) {
          options.display.components.clock = displayOptions.timePicker
        }
        if (undefined !== displayOptions.timePicker24Hour && displayOptions.timePicker24Hour) {
          options.localization.hourCycle = 'h23'
        } else {
          options.localization.hourCycle = 'h12'
        }
        if (undefined !== displayOptions.showWeekNumbers && displayOptions.showWeekNumbers) {
          options.display.calendarWeeks = true
        }
        if (undefined !== displayOptions.timePickerIncrement) {
          options.stepping = displayOptions.timePickerIncrement
        }
      }
      if (pickers[i].dataset.minuteStep) {
        options.stepping = JSON.parse(pickers[i].dataset.minuteStep)
      }
      const picker = new TempusDominus(pickers[i], options)
      if (pickers[i].dataset.dateLocale) {
        picker.locale(pickers[i].dataset.dateLocale)
      }
      pickers[i].addEventListener('change.td', function () {
        if (pickers[i].classList.contains('ignore-ems-update')) {
          return
        }
        const event = new ChangeEvent(pickers[i])
        event.dispatch()
      })
    }
  }

  defaultOptions() {
    return {
      display: {
        buttons: {
          today: true,
          clear: true,
          close: true
        },
        components: {}
      },
      localization: {
        startOfTheWeek: 1
      },
      restrictions: {}
    }
  }
}

export default Datetime
