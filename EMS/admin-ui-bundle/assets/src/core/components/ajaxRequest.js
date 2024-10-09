import $ from 'jquery'
import notifications from './notifications'

class AjaxRequest {
  initRequest() {
    notifications.startActivity()
  }

  private_begin_response() {
    notifications.stopActivity()
  }

  post(url, data, modal) {
    this.initRequest()
    const self = this

    const out = new (function () {
      this.success = function (callback) {
        this.successFct = callback
        return this
      }

      this.fail = function (callback) {
        this.failFct = callback
        return this
      }

      this.always = function (callback) {
        this.alwaysFct = callback
        return this
      }

      const xhr = $.post(url, data)
        .done(function (data) {
          const response = self.treatResponse(data, modal)
          if (response.success) {
            if (out.successFct) {
              out.successFct(response)
            }
          } else {
            if (out.failFct) {
              out.failFct(response)
            }
          }
          if (out.alwaysFct) {
            out.alwaysFct(response)
          }
        })
        .fail(function (event, data) {
          if (!data || !data.aborted) {
            self.requestFailed()
          }
        })

      this.abortFct = xhr.abort

      this.abort = function () {
        self.private_begin_response()
        out.abortFct({ aborted: true })
      }
    })()

    return out
  }

  get(url, data, modal) {
    this.initRequest()
    const self = this

    const out = new (function () {
      this.success = function (callback) {
        this.successFct = callback
        return this
      }

      this.fail = function (callback) {
        this.failFct = callback
        return this
      }

      this.always = function (callback) {
        this.alwaysFct = callback
        return this
      }

      const xhr = $.get(url, data)
        .done(function (data) {
          const response = self.treatResponse(data, modal)
          if (response.success) {
            if (out.successFct) {
              out.successFct(response)
            }
          } else {
            if (out.failFct) {
              out.failFct(response)
            }
          }
          if (out.alwaysFct) {
            out.alwaysFct(response)
          }
        })
        .fail(function (event, data) {
          if (data && data.aborted) {
            //        console.log('post aborted');
          } else {
            self.requestFailed()
          }
        })

      this.abortFct = xhr.abort

      this.abort = function () {
        self.private_begin_response()
        out.abortFct({ aborted: true })
      }
    })()

    return out
  }

  treatResponse(data, modal) {
    this.private_begin_response()
    try {
      let response = data
      if (typeof data === 'string') {
        response = JSON.parse(data)
        console.log('An AJAX call did not returned a JSON')
      }

      if (modal) {
        $('#' + modal).modal('show')
      }

      if (response.success) {
        notifications.addActivityMessages(response.notice)
        notifications.addWarningMessages(response.warning)
        notifications.addErrorMessages(response.error)
      } else {
        notifications.addNoticeMessages(response.notice)
        notifications.addWarningMessages(response.warning)
        notifications.addErrorMessages(response.error)
      }
      return response
    } catch (e) {
      console.log(e)
    }
    notifications.outOfSync()
    return null
  }

  requestFailed(e) {
    console.log(e)
    this.private_begin_response()
    notifications.outOfSync()
  }
}

const ajaxRequest = new AjaxRequest()

export default ajaxRequest
