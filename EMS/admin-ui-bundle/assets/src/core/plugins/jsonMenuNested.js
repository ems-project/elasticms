import JsonMenuNestedComponent from '../components/jsonMenuNestedComponent'

class JsonMenuNested {
  constructor() {
    window.jsonMenuNestedComponents = []
  }

  load(target) {
    const elements = target.getElementsByClassName('json-menu-nested-component')
    ;[].forEach.call(elements, function (element) {
      const component = new JsonMenuNestedComponent(element)
      if (component.id in window.jsonMenuNestedComponents)
        throw new Error(`duplicate id : ${component.id}`)
      window.jsonMenuNestedComponents[component.id] = component
    })
  }
}

export default JsonMenuNested
