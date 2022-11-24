# Supported fields

## Overview

### Common fields
* [Checkbox](#checkbox)
* [ChoiceCheckboxes](#choicecheckboxes)
* [ChoiceRadios](#choiceradios)
* [ChoiceSelect](#choiceselect)
* [ChoiceSelectMultiple](#choiceselectmultiple)
* [Date](#date)
* [DateWithPicker](#datewithpicker)
* [Email](#email)
* [EmailWithConfirmation](#emailwithconfirmation)
* [File](#file)
* [MultipleFile](#multiplefile)
* [Number](#number)
* [NumberForgivingInput](#numberforgivinginput)
* [Phone](#phone)
* [Text](#text)
* [Textarea](#textarea)
* [Time](#time)

### Custom fields
* [CompanyNumber](#companynumber)
* [CompanyNumberMultiple](#companynumbermultiple)
* [Markup](#markup)
* [NissInsz](#nissinsz)
* [OnssRsz](#onssrsz)
* [Submit](#submit)


## Fields

### Checkbox <a name="checkbox"/>
A single checkbox field, that can be turned on and off again.

### Choice Checkboxes <a name="choicecheckboxes"/>
A list of values that can be chosen from using a checkbox layout. The end user is allowed to choose multiple values.

See [Choice Select](#choiceselectpassing) for information on passing values, labels and a placeholder to this field.

### Choice Radios <a name="choiceradios"/>
A list of values that can be chosen from using a radio button layout. The end user can only select one value.

See [Choice Select](#choiceselectpassing) for information on passing values, labels and a placeholder to this field.

### Choice Select <a name="choiceselect"/>
A list of values that can be chosen from using a select box. The end user can only select one value.

#### Passing values, labels, and a placeholder to this field <a name="choiceselectpassing"/>
Choices are passed via the "choices" content type. The content type contains a list of values and labels. These should be formatted as a json array.
A simple choice value list would look like this:
```json
[
  "dossier",
  "demand",
  "document",
  "information",
  "complaint",
  "other"
]
```

A multi level value list would look as follows (demonstrating 3 levels!):
```json 
[
  { "dossier": [
    "dossier-a",
    { "dossier-b": ["dossier-b-1", "dossier-b-2"]},
    { "dossier-c": ["dossier-c-1", "dossier-c-2"]}
  ]},
  { "demand":  [
    "demand-a",
    { "demand-b": ["demand-b-1"]}
  ]},
  "document",
  "information",
  { "complaint":  [
    { "complaint-a": ["complaint-a-1", "complaint-a-2"]},
    { "complaint-b": ["complaint-b-1", "complaint-b-2"]},
    { "complaint-c": ["complaint-c-1", "complaint-c-2"]}
  ]},
  "other"
]
```

If you would pass a multi level choice when this is not supported, only the first level will be used. Only "Choice Select" supports multi level choices.

Passing a placeholder label "Choose an option" for your choice list is as simple as adding it to your label list as the first argument:
```json
[
  "Choose an option",
  "Your file",
  "Your demand",
  "Your document",
  "Your information",
  "A plaint",
  "Other"
]
```
#### Duplicate values
If your choice contains duplicate values, they will be transformed to unique integer strings. Having duplicate values should therefore be catched and transformed in the submit twig template.

For example, the value list
```json
[
  "true",
  "false",
  "false",
]
```
Will be dynamically transformed to
```json
[
  "0",
  "1",
  "2",
]
```

### Choice Select Multiple <a name="choiceselectmultiple"/>
A list of values that can be chosen from using a select box. The end user is allowed to choose multiple values.

See [Choice Select](#choiceselectpassing) for information on passing values, labels and a placeholder to this field.

### Date <a name="date"/>
A single text to enter a date with a defined format : dd/mm/yyyy and save as string.

### DateWithPicker <a name="datewithpicker"/>
A single text to enter a date with a defined format : dd/mm/yyyy and save as string.
A custom class : 'date-with-picker' is available and can be used by your frontend to activate and style the datepicker plugin of your choice.
The css and javascript for this datepicker are the responsability of the site that integrates the form, as there are too much options for the different frameworks out there.

### Email <a name="email"/>
Ensure that the end user's input is a valid email address.

### Email With Confirmation <a name="emailwithconfirmation"/>
Ensure that the end user's input is a valid email address. And provides a second field in which the end user needs to validate the given address.
This field is designed to prevent pasting values in both the original and repeated email field.

### File <a name="file"/>
Allow an end user to upload a file.

### MultipleFile <a name="multiplefile"/>
Allow an end user to upload multiple files.

### Number <a name="number"/>
This field only allows integers as input.

### Number Forgiving Input <a name="numberforgivinginput"/>
Instead of using a "Numbers" field, you can use this field to have a forgiving input format. Your users can make errors or separate the number how they want. The data being validated and send are the numbers in this text field.

### Phone <a name="phone"/>
A field that's used for phone input, as per html standard no validations happen by default on this field.

### Text <a name="text"/>
A simple field for text input (one line).

### Textarea <a name="textarea"/>
A simple field for large text input (multiple lines).

### Time <a name="time"/>
A field to define a time with a text input with format HH:mm.


### CompanyNumber <a name="companynumber"/>
A text field designed ton combine with CompanyNumber validation. The frontend will automatically activate validation when both this field and it's validation are combined.

### CompanyNumberMultiple <a name="companynumbermultiple"/>
A textarea field designed ton combine with CompanyNumberMultiple validation. The frontend will automatically activate validation when both this field and it's validation are combined.

### Markup <a name="markup"/>
This field is special, as it is not a real form field. The `Markup` field allows you to introduce text between fields in your form.
The end user cannot change the value of this field, and the static data of this 'field' is not processed on submit.

### NissInsz <a name="nissinsz"/>
A text field designed to combine with the NissInsz validation. The frontend will automatically activate validation when both this field and it's validation are combined.

### OnssRsz <a name="onssrsz"/>
A text field designed to combine with the NSSO validation. The frontend will automatically activate validation when both this field and it's validation are combined.

### Submit <a name="submit"/>
Make sure to add this field at the end of your form to allow the submission of your data to the server!

## Why are we defining custom fields for our validations that can operate on the Text field type ?
You might notice that we develop fields like "niss-insz" that only diverge from the Text field in the id they provide. ('niss-insz' in this case).
We use this to have a link between our form field class in the HTML code and the class used by our Javascript to automatically instantiate javascript validation.
