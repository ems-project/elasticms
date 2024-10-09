import $ from 'jquery'
import ajaxRequest from '../components/ajaxRequest'

class Job {
  load(target) {
    this.loadStartJob(target)
    this.loadRequestJob(target)
  }

  loadStartJob(target) {
    $(target)
      .find('[data-start-job-url]')
      .each(function () {
        $.ajax({
          type: 'POST',
          url: this.getAttribute('data-start-job-url')
        }).always(function () {
          location.reload()
        })
      })
  }

  loadRequestJob(target) {
    $(target)
      .find('a.request_job')
      .on('click', function (e) {
        e.preventDefault()
        ajaxRequest.post($(e.target).data('url')).success(function (message) {
          ajaxRequest.post(message.jobUrl)
          $('ul#commands-log').prepend(
            '<li title="Job ' +
              message.jobId +
              '">' +
              '<a href="' +
              message.url +
              '" >' +
              'Job #' +
              message.jobId +
              '</a>' +
              '</li>'
          )
        })
      })
  }
}

export default Job
