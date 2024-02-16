class Text {
  load (target) {
    this.loadTextCounter(target)
  }

  loadTextCounter (target) {
    const spans = target.querySelectorAll('.text-counter[data-counter-label]')
    for (let i = 0; i < spans.length; ++i) {
      const span = spans[i]
      const input = span.parentElement.querySelector('textarea,input')
      if (null === input) {
        return
      }
      const counterLabel = span.dataset.counterLabel
      const updateCounter = function () {
        const length = input.value.length
        span.textContent = counterLabel.replace('%count%', length)
      }
      input.addEventListener('keyup', function () {
        updateCounter()
      })
      updateCounter()
    }
  }
}

export default Text
