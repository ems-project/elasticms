import {TiptapModule} from "../types.ts";

export const stylesModule: TiptapModule = {
    toolbarGroup: 'styles',
    toolbar: [
        {
            create: () => createStylesDropDown(),
        }
    ]
}

function createStylesDropDown(): HTMLElement {
    const wrapper = document.createElement('div')
    wrapper.className = 'tiptap-styles-dropdown'

    const button = document.createElement('button')
    button.type = 'button'
    button.className = 'tiptap-styles-btn'
    button.innerHTML = '<span class="styles-label">Styles</span><span>▾</span>'

    wrapper.appendChild(button)

    return wrapper
}