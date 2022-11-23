import {i18n} from "../modules/translations";

export function addMaxLengthCounter(element) {
    let parent = element.parentElement;
    let max = parseInt(element.getAttribute('data-maxlength'));

    let spanCounter = document.createElement("small");
    spanCounter.setAttribute('aria-hidden', 'true');
    parent.appendChild(spanCounter);

    maxLengthCounterDiff();

    element.addEventListener('keyup', maxLengthCounterDiff);
    element.addEventListener('paste', function(){
      // https://stackoverflow.com/questions/13895059/how-to-alert-after-paste-event-in-javascript
      setTimeout(function(){maxLengthCounterDiff()}, 0);
    });

    function maxLengthCounterDiff() {
      let diff = max - parseInt(element.value.length);
      spanCounter.innerText = i18n.trans('max_length_count', {'count': diff});

      if (diff < 0) {
        spanCounter.classList.add("text-danger");
      } else {
        spanCounter.classList.remove("text-danger");
      }
    }
}