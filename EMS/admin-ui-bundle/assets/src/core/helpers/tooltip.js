import { Tooltip } from 'bootstrap'

function tooltipDataLinks(target) {
  target.querySelectorAll('[data-toggle="tooltip"]').forEach((element) => new Tooltip(element))
}

export { tooltipDataLinks }
