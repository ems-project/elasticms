import {i18n} from './../modules/translations'
import {MaxFileSizeValidator} from './maxFileSizeValidator'

export function setMaxFileSizeValidation(element) {
  const validation = function(e) {
    if(this.value === '') {
      this.setCustomValidity('')
      return
    }

    const maxAllowedSize = parseInt(element.dataset.maxfilesize)
    const validator = new MaxFileSizeValidator(e.target.files, maxAllowedSize)

    if (validator.validate()) {
      this.setCustomValidity('')
    } else {
      const humanMaxAllowedSize = parseInt(maxAllowedSize/1000/1000)
      const translation = (validator.hasMultipleFiles()) ? 'max_multiple_file_size' : 'max_single_file_size'
      this.setCustomValidity(i18n.trans(translation, {max_allowed_size: humanMaxAllowedSize}))
    }
  }

  element.addEventListener('change', validation)
}