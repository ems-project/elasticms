import $ from 'jquery'

class Job {
  load (target) {
    $(target).find('[data-start-job-url]').each(function () {
      $.ajax({
        type: 'POST',
        url: this.getAttribute('data-start-job-url')
      }).always(function () {
        location.reload()
      })
    })
  }
}

export default Job
