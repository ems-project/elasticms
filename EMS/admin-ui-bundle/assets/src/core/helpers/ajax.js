/*
 @TODO deprecated, please use fetchApi see ajaxModal.js
 */

function ajaxJsonGet(url, onready) {
  const httpRequest = new XMLHttpRequest()
  httpRequest.open('GET', url, true)
  httpRequest.setRequestHeader('Content-Type', 'application/json')
  _sendRequest(httpRequest, onready)
}

function ajaxJsonPost(url, data, onready) {
  const httpRequest = new XMLHttpRequest()
  httpRequest.open('POST', url, true)
  httpRequest.setRequestHeader('Content-Type', 'application/json')
  _sendRequest(httpRequest, onready, data)
}

function ajaxJsonSubmit(url, formData, onready) {
  const httpRequest = new XMLHttpRequest()
  httpRequest.open('POST', url, true)
  httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  _sendRequest(httpRequest, onready, formData)
}

function _sendRequest(httpRequest, onready, data) {
  httpRequest.onreadystatechange = () => {
    if (httpRequest.readyState === XMLHttpRequest.DONE) {
      const json = JSON.parse(httpRequest.responseText)
      if (typeof onready === 'function') {
        onready(json, httpRequest)
      }
    }
  }
  httpRequest.send(data)
}

export { ajaxJsonGet, ajaxJsonPost, ajaxJsonSubmit }
