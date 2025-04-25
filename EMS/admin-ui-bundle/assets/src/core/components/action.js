export default class Action {
  load(target) {
    const revisionFieldActions = target.querySelectorAll('.field-action');
    revisionFieldActions.forEach(button => onClickRevisionFieldAction(button));
  }
}

function onClickRevisionFieldAction(button) {
  button.addEventListener('click', event => {
    event.preventDefault();

    const { fieldId, revisionId } = button.dataset;
    if (!fieldId || !revisionId) return;

    sendAction(`/action/revision/${revisionId}/field/${fieldId}`)
      .then((json) => handle(json))
      .catch((error) => console.error(error));
  });
}

function handle({ outputFields }) {
     outputFields.forEach((name) => {
        const element = document.querySelector(`[name="${name}"]`);
        if (!element) return;

        if (element.id in CKEDITOR.instances) {
          const ckeditor = CKEDITOR.instances[element.id]
          ckeditor.setReadOnly(true);
        } else {
          element.disabled = true;
        }
    });
}

async function sendAction(endpoint) {
  const response = await fetch(endpoint, {
    method: 'GET',
    headers: { 'Content-Type': 'application/json' }
  });

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  return response.json();
}