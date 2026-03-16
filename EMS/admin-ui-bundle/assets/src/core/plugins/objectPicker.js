import ChoicePicker from '../helpers/choicePicker.js'

export default class ObjectPicker {
    load(target) {
        const elements = target.querySelectorAll('.objectpicker')
        for (let i = 0; i < elements.length; ++i) {
            new ChoicePicker(elements[i])
        }
    }
}
