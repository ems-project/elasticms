export default function collapse() {
  document.querySelectorAll('.collapsible').forEach((wrapper) => {
    const button = wrapper.querySelector('.btn-collapse')
    if (button === null) {
      return
    }

    const collapse = wrapper.querySelectorAll(':scope > .collapse')
    let hasContent = false
    let defaultExpanded = false

    collapse.forEach((element) => {
      hasContent = !!element.firstElementChild
      defaultExpanded = !!(element.style.display === 'block' && defaultExpanded === false)
    })

    button.setAttribute('aria-expanded', defaultExpanded)

    if (!hasContent) {
      button.style.display = 'none'
      button.onclick = () => {}
    } else {
      button.style.display = 'inline-block'
      button.onclick = (event) => {
        event.preventDefault()
        const expanded = button.getAttribute('aria-expanded')
        button.setAttribute('aria-expanded', expanded === 'true' ? 'false' : 'true')
        collapse.forEach((c) => {
          c.style.display = expanded === 'true' ? 'none' : 'block'
        })
      }
      button.addEventListener('show', (evt) => {
        evt.preventDefault()
        evt.target.setAttribute('aria-expanded', 'true')
        collapse.forEach((c) => {
          c.style.display = 'block'
        })
      })
      button.addEventListener('hide', (evt) => {
        evt.preventDefault()
        evt.target.setAttribute('aria-expanded', 'false')
        collapse.forEach((c) => {
          c.style.display = 'none'
        })
      })
    }
  })
}
