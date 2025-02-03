import JsonMenuNestedComponent from '../components/jsonMenuNestedComponent'

class JsonMenuNested {
  load(target: HTMLElement) {
    const elements = target.getElementsByClassName('json-menu-nested-component')

    if (!window.jsonMenuNestedComponents) {
      window.jsonMenuNestedComponents = {}
    }

    [].forEach.call(elements, function (element) {
      const component = new JsonMenuNestedComponent(element)
      if (component.id in window.jsonMenuNestedComponents) {
        throw new Error(`duplicate id : ${component.id}`)
      }
      window.jsonMenuNestedComponents[component.id] = component
    })

    document.addEventListener('jmn.copy', (e: any) => {
      Object.values(window.jsonMenuNestedComponents).forEach((component) =>
        component.onCopy(e.detail)
      )
    })
  }
}

export default JsonMenuNested
