import '../css/iframe.scss'

let loaded = false
const resizeFct = function () {
  if (!loaded) {
    return
  }
  window.parent.postMessage('resize', document.body.dataset.targetOrigine)
}
const startReady = function () {
  if (loaded) {
    return
  }
  window.parent.postMessage('ready', document.body.dataset.targetOrigine)
  setTimeout(startReady, 1000)
}

window.addEventListener('message', function (event) {
  if (event.source !== window.parent) {
    console.log('Not a parent\'s message')
    return
  }
  loaded = true
  document.body.insertAdjacentHTML('afterbegin', event.data)
  resizeFct()
})
window.addEventListener('resize', resizeFct)
window.addEventListener('redraw', resizeFct)
window.addEventListener('load', resizeFct)
startReady()
