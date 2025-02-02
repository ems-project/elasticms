import 'formdata-polyfill'

export default class
{
    static getObjectFromFormData(form)
    {
        let object = {};
        let formData = new FormData(form);

        formData.forEach((value, key) => {
            if(!Object.prototype.hasOwnProperty.call(object, key)){
                object[key] = value;
                return;
            }
            if(!Array.isArray(object[key])){
                object[key] = [object[key]];
            }
            object[key].push(value);
        });

        return object;
    }

    static getFormDataFromObject(obj)
    {
        let formData = new FormData();
        Object.entries(obj).forEach(([key,value])=>{
            if (value.size === 0) {
                return;
            }
            let filename = value.name;
            if (filename !== undefined) {
                if (value.size > 0) {
                    formData.set(key, value, filename);
                }
                return;
            }

            if(Array.isArray(value)){
                for (let i = 0; i < value.length; i++) {
                    formData.append(key, value[i]);
                }
                return;
            }

            formData.set(key, value);
        });

        return formData;
    }

    static disablingSubmitButton(form)
    {
        let submits = form.getElementsByClassName('submit');
        Array.prototype.forEach.call(submits, function(submit) {
            submit.disabled = true;
        });
    }
}
