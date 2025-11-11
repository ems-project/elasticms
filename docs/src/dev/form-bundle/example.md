# Example implementation
Requirements:
- 1 elasticms backend [https://emsforms.example]
- 1 elasticms skeleton [https://emsforms-skeleton.example]
- 1 website where you integrate the form [https://your-website.example]
## Elements to integrate on your website
Your html page on https://your-website.example needs the following **4 elements** to integrate an EMSForm.

1. An `iframe` to communicate to [https://emsforms-skeleton.example] 
1. Two empty `div`s, one to render the form, and one to render the submission response that are fetched from the iframe
1. Our javascript file `form.js` that handles all communication and rendering for you!

### The iframe
The iframe is a blanc page with javascript only. (You should hide it using css on your website).
This iframe is used for sending messages using the [postMessage](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage) protocol to [https://emsforms-skeleton.example] from and to your website. 

The html code has the following structure:
```html
<iframe id="ems-form-iframe" src="https://emsforms-skeleton.example/iframe/{myCommunicationId}/{lang}"></iframe>
```
Make sure to use the id `"ems-form-iframe"` as this is used by our javascript file to automate communication.

Replace `{myCommunicationId}` by the ouuid of your form, which is defined in the backend [https://emsforms.example]. 
The iframe is protected as defined by the [postMessage](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage) protocol, meaning that your website's domain name should be registerd in the form configuration of the backend [https://emsforms.example].
When sending messages to the iframe, you will only get a response if your domain is allowed to communicate!

Replace `{lang}` by the language identifier of your form. `nl`, `fr`, `en`, `de`, ...

### The empty divs
In the first div container the form will be placed. You can style this element however you want, add classes, ...
```html
<div id="ems-form"></div>
```
Make sure to use the id `"ems-form"` as this is used by our javascript file to automate the form rendering.

In the second div container the message after valid submission will be placed. This div should be hidden as it will contain a json response that can be used to decide what you do on your site after submitting a form.
```html
<div id="ems-message"></div>
```
Make sure to use the id `"ems-message"` as this is used by our javascript file to automate the form rendering.

### The javascript file
Include the javascript file "form.js" for sending and receiving [postMessage](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage) between your website [https://your-website.example] and the form skeleton [https://emsforms-skeleton.example].
```html
<script type="application/javascript" src="https://emsforms-skeleton.example/bundles/emsform/js/form.js"></script>
```

#### Form validation on the frontend
Whenever supported the bundle implements html5 attributes to validate the form fields on the frontend.

However, some fields are custom, and require custom validation. The form.js file uses the generated form field class attributes to initialize these javascript validations automatically.

## Full example in twig

```twig
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Example emsForm</title>
    </head>
<body>
    <div id="wrapper">
        <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla posuere velit quis elit rutrum,
            eu ornare dui cursus. Maecenas rhoncus velit justo. Vestibulum eleifend nunc ut lorem malesuada,
            id pellentesque tortor mollis.
        </p>
        <div id="ems-form"></div>
        <div id="ems-message"></div>
    </div>
    <iframe id="ems-form-iframe" src="https://emsforms-skeleton.example/iframe/{ouuid}/{locale}"></iframe>
    <script src="{{ 'https://emsforms-skeleton.example/bundles/emsform/bundles/emsform/js/form.js"></script>
</body>
</html>
```

## Custom implementation
If you want to initialize the form and validations yourself, you can simply change the id's of your `iframe` and `empty div`.
The following twig script shows you how to achieve this; 

```twig
    <script type="application/javascript" src="{{ 'https://emsforms-skeleton.example/bundles/emsform/bundles/emsform/js/form.js' }}"></script>
    <script type="application/javascript">
        new emsForm({ 'idForm': 'form-custom', 'idMessage': 'message-custom', 'idIframe': 'ems-form-iframe-custom', 'ouuid': '{ouuid}'}).init();
        new emsForm({ 'idForm': 'form-custom-second', 'idMessage': 'message-custom-second', 'idIframe': 'ems-form-iframe-custom-second', 'ouuid': '{ouuid2}', onLoad: function(){ console.log('foobar') }, defaultData: {'surname': 'Doe'} }).init();
    </script>
 ```

This code assumes that two of our iframes are loaded: `ems-form-iframe-custom` and `ems-form-iframe-custom-second` each containing another form id.
For each iframe, an `emsForm` object is created for which we configure the `idForm`, `idMessage` and `idIframe` options to the id's used in our page.
In order to avoid misrouting of the messages to the wrong form it's important to specify in the `ouuid` attribute the form's instance ouuid

* idForm corresponds to the empty div that can be used to render the form fetched through the corresponding iframe.
* idMessage corresponds to the empty div that can be used for the messages returned after valid submit through the corresponding iframe.
* idIframe corresponds to the iframe used to communicate (same value as used by the getElementById function).
* ouuid corresponds to the id of the form instance, this attribute is required if you plan to instantiate more than one form on the same page
* defaultData: is an optional attribute that can specify a default content for the form (JS object format)

## Callback functions

* **onLoad** callback function called once the form has just been initialize
* **onSubmit** function called when the form's data are sent to the backend
* **onResponse** callback function called when a response is available
* **onError** callback function called when a error appends

Here is an example where the callback functions are used to handle the different elasticms form's events with a form having a bootstrap template:

```twig
    <script type="application/javascript">
        new emsForm({ 
            'idForm': 'form-custom', 
            'idMessage': 'message-custom', 
            'idIframe': 'ems-form-iframe-custom',
            'onLoad': function() {
                console.log('My onload function');
                $(".custom-file-input").on("change", function() {
                  var fileName = $(this).val().split("\\").pop();
                  $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
                });
            },
            'onSubmit': function() {
                console.log('My submit function');
                $('#form-custom').find('input,button').attr('disabled',true);
                
            },
            'onError': function(errorMessage) {
                console.log('My error function:' + errorMessage);
                $('#form-custom').html("{{ 'form.submit_error'|trans|e('js') }}".replace('%error%', errorMessage));
            },
            'onResponse': function(json) {
                console.log('My response function');
                var response = JSON.parse(json);
                $('#form-custom').html("{{ 'form.submitted'|trans|e('js') }}");
                var li = '';
                $.each(response, function(i) {
                    var submit = JSON.parse(this);
                    if (submit.status === 'success') {
                        li += "<li>{{ 'form.success'|trans|e('js') }}</li>".replace('%data%', submit.data);
                    }
                    else {
                        li += "<li>{{ 'form.failed'|trans|e('js') }}</li>".replace('%data%', submit.data, '%status%', submit.status);
                    }
                });
                $('#form-custom').append( "<ul>" + li + "</ul>" );
            },
            'onConfirmationResponse': function(response) {
                console.log('Response:', response); 
            },
        }).init();
    </script>
```

The `onLoad` callback function can be used to attached event listeners to the just loaded form. Like attach the Bootstrap custom file upload listener in this example.

The `onSubmit` can be used to give a feedback to the end user that the submission is in progress by, like here, disabling all fields. Or why not, a visual feedback (i.e. a spinning wheel replacing the form)

The `onResponse` is called when the submission is done. This callback function receive a json-encoded message from the different submission services triggered by the submission.

The `onError` is called when an error as been raised. This callback function receives an error message as unique parameter.
 
The `onConfirmationResponse` is called after a send confirmation request.
 
## The response after a valid submit
The system allows to handle your submit by multiple chained handlers. Each handler will return a json response with two keys:

* `status`: indicating if the handler succeeded () or not (`error`).
* `data`: the data returned by the submit handler.

Example response of a failed handler:
```json 
{
    "status":"error",
    "data":"Submission failed, contact your admin. Notice: Undefined index: from"
}
```

These responses will be made available after submit in the `ems-message` div as a json array:
```html
<div id="ems-message">
    ["{\"status\":\"error\",\"data\":\"Submission failed, contact your admin. Notice: Undefined index: from\"}"]
</div>
```

