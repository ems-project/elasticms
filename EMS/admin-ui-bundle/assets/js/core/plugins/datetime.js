import { TempusDominus } from '@eonasdan/tempus-dominus'
import '@eonasdan/tempus-dominus/src/scss/tempus-dominus.scss'
import ChangeEvent from '../events/changeEvent'

class Datetime {
  #iframes = []

  load (target) {
      this.loadDatetime(target)
      this.loadDate(target)
  }

  loadDate (target) {
      this.loadPicker(target, '.datepicker', {
          display: {
              buttons: {
                  today: true,
                  clear: true,
                  close: true,
              },
              components: {
                  clock: false
              },
          },
          localization: {},
          restrictions: {},
          multipleDatesSeparator: ','
      })
  }

  loadDatetime (target) {
      this.loadPicker(target, '.datetime-picker', {
          display: {
              buttons: {
                  today: true,
                  clear: true,
                  close: true,
              }
          },
          localization: {
              startOfTheWeek: 1
          },
          restrictions: {}
      })
  }

  loadPicker (target, query, options) {
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
          options.restrictions.daysOfWeekDisabled = JSON.parse(pickers[i].dataset.dateDaysOfWeekDisabled)
      }
      if (pickers[i].dataset.dateDisabledHours) {
          options.restrictions.disabledHours = JSON.parse(pickers[i].dataset.dateDisabledHours)
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
}

export default Datetime
