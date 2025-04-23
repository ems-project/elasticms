export default class Action {
  load(target) {
    const fieldActionButtons = target.querySelectorAll('.field-action');
    fieldActionButtons.forEach(button => onClickFieldAction(button));
  }
}

function onClickFieldAction(button) {
  button.addEventListener('click', event => {
    event.preventDefault();

    const fieldId = button.dataset.fieldId;
    if (!fieldId) return;

    sendAjaxRequest(fieldId)
      .then((json) => console.debug(json))
      .catch((error) => console.error(error));
  });
}

async function sendAjaxRequest(fieldId) {
  const response = await fetch(`/action/field/${fieldId}`, {
    method: 'GET',
    headers: { 'Content-Type': 'application/json' }
  });

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  return response.json();
}