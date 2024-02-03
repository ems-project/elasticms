import $ from 'jquery'

class Notifications {
    constructor () {
        this.counter = 0
    }

    startActivity() {
        if (++this.counter > 0) {
            $('#ajax-activity').addClass('fa-spin')
        }
    }

    stopActivity() {
        if (--this.counter === 0) {
            $('#ajax-activity').removeClass('fa-spin')
        }
    }

    addActivityMessages(messages) {
        if (!Array.isArray(messages) || 0 === messages.length) {
            return;
        }
        const activityList = $('ul#activity-log')
        for (let index = 0; index < messages.length; ++index) {
            const message = $($.parseHTML(messages[index]))
            activityList.append(`<li title="${message.text()}">${messages[index]}</li>`)
        }
        this.updateCounter()
    }

    updateCounter() {
        const numberOfElem = $('ul#activity-log>li').length
        console.log(numberOfElem)
        if (numberOfElem) {
            $('#activity-counter').text(numberOfElem)
        } else {
            $('#activity-counter').empty()
        }
    }

}

const notifications = new Notifications()

export default notifications
