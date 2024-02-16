import { TempusDominus } from '@eonasdan/tempus-dominus'
import '@eonasdan/tempus-dominus/src/scss/tempus-dominus.scss'
import ChangeEvent from '../events/changeEvent'

class Datetime {
  #iframes = []

  load (target) {
    const datetimePickers = target.querySelectorAll('.datetime-picker')
    for (let i = 0; i < datetimePickers.length; i++) {
      const picker = new TempusDominus(datetimePickers[i], {
        display: {
          buttons: {
            today: true,
            clear: true,
            close: true
          }
        },
        localization: {
          format: datetimePickers[i].dataset.dateFormat,
          startOfTheWeek: 1
        },
        restrictions: {
          daysOfWeekDisabled: JSON.parse(datetimePickers[i].dataset.dateDaysOfWeekDisabled),
          disabledHours: JSON.parse(datetimePickers[i].dataset.dateDisabledHours)
        }
      })
      if (datetimePickers[i].dataset.dateLocale) {
        picker.locale(datetimePickers[i].dataset.dateLocale)
      }
      datetimePickers[i].addEventListener('change.td', function () {
        if (datetimePickers[i].classList.contains('ignore-ems-update')) {
          return
        }
        const event = new ChangeEvent(datetimePickers[i])
        event.dispatch()
      })
    }
  }
}

export default Datetime
