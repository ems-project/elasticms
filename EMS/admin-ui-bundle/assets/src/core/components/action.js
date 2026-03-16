export default class Action {
    load(target) {
        const revisionForm = target.querySelector('form[name="revision"]')
        if (!revisionForm) return

        const revisionId = revisionForm.dataset.revisionId
        if (!revisionId) return

        const revisionFieldActions = target.querySelectorAll('.field-action')
        revisionFieldActions.forEach((button) => onClickRevisionFieldAction(button))

        if (revisionFieldActions.length > 0) {
            setupMercure(revisionId)
        }
    }
}

function setupMercure(targetRevisionId) {
    fetch('/mercure/token')
        .then((response) => response.json())
        .then((data) => {
            const url = new URL(data.url)
            url.searchParams.append('authorization', data.token)
            data.topics.forEach((topic) => url.searchParams.append('topic', topic))

            const eventSource = new EventSource(url)
            eventSource.onmessage = (event) => {
                const { revisionId, response } = JSON.parse(event.data)
                if (revisionId === targetRevisionId) applyResponse(response)
            }
            eventSource.onerror = (error) => {
                console.error('EventSource failed:', error)
            }
        })
}

function applyResponse(response) {
    Object.entries(response).forEach(([key, value]) => {
        const element = document.querySelector(`[name="revision[data]${key}"]`)
        if (!element) return

        const editor = CKEDITOR?.instances?.[element.id]

        if (editor) {
            editor.setReadOnly(false)
            editor.setData(value)
        } else if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
            element.value = value
            element.disabled = false
        } else if (element.tagName === 'SELECT') {
            element.value = value
            element.disabled = false
            element.dispatchEvent(new Event('change', { bubbles: true }))
        }
    })
}

function onClickRevisionFieldAction(button) {
    button.addEventListener('click', async (event) => {
        event.preventDefault()

        const { fieldId, revisionId } = button.dataset
        if (!fieldId || !revisionId) return

        try {
            const response = await fetch(`/action/revision/${revisionId}/field/${fieldId}`)
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`)
            }
            const json = await response.json()
            disableOutputFields(json)
        } catch (error) {
            console.error('Failed to send action:', error)
        }
    })
}

function disableOutputFields({ outputFields = [] }) {
    outputFields.forEach((name) => {
        const element = document.querySelector(`[name="revision[data]${name}"]`)
        if (!element) return

        const editor = CKEDITOR?.instances?.[element.id]
        if (editor) {
            editor.setReadOnly(true)
        } else {
            element.disabled = true
        }
    })
}
