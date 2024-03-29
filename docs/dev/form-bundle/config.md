# Configuration
## config file (config/packages/ems_form.yaml)
```yaml
ems_form:
    hashcash_difficulty: '%env(int:EMSF_HASHCASH_DIFFICULTY)%'
    endpoints: '%env(json:EMSF_ENDPOINTS)%'
    instance:
        type: form_instance
        form-field: form
        theme-field: theme_template
        submission-field: submissions
        type-form-field: form_structure_field
        type-form-markup: form_structure_markup
        type-form-subform: form_structure
```

## Hashcash Difficulty
By default hashcash is enable because of the default value (16384) for the configuration setting **ems_form.hashcash_difficulty**.
This value can be overwritten by setting the environment variable **EMSF_HASHCASH_DIFFICULTY**.

For disabling hashcash set the difficulty value to 0.

To compute the complexity you can use this algorithm (or check the table bellow):

```php
<?php
$numberOfPrefixedZero = 5;
echo pow(2, 4*$numberOfPrefixedZero);
```

Basically, the proof of work is base on the probably to find a random string in order to get a hash starting by a certain number of leading zeros.

The complexity is the average number of hash to compute in order to get a such random string.

| Leading zeros                | Complexity    |
|------------------------------|---------------|
| 0 (hashcash is desactivated) | 0             |
| 1                            | 16            |
| 2                            | 256           |
| 3                            | 4096          |
| 4                            | 65536         |
| 5                            | 1048576       |
| 6                            | 16777216      |
| 7                            | 268435456     |
| 8                            | 4294967296    |
| 9                            | 68719476736   |
| 10                           | 1099511627776 |
| ...                          | ...           |

## Endpoints

For now this endpoints config is use for sending a confirmation. 

###Http request body replacements

The following placeholder(s) can be used in the body and will be replaced.
- **%verification_code%**: replaced by the verification code
- **%value%: replaced by** the passed value
- **%message_translation%**: replaced by the translated value of the defined property *'message_translation_key'*

example 1 endpoint configuration for send_confirmation form field:
```json
[
  {
    "field_name": "send_confirmation",
    "message_translation_key": "translation_key",
    "http_request": {
      "url": "https://api.example.test/v1/send/sms",
      "headers": {
        "Content-Type": "application/json"
      },
      "body": "{\"To\": \"%value%\", \"Message\": \"%message_translation_key%\", , \"Example\": \"%verification_code%\"}"
    }
  }
]
```




## Instance
The instance options allows you to configure you form, fields, validations, and security! The config shown above is the default, and informs the system how the content types in ElasticMS are layed out.

### Type

```yaml
instance:
    type: form_instance
```
This definition is the entry point of our form. To expose a form for integration on a website you will have to use the ouuid of the `form_instance` object that you created.
The `form_instance` content type groups all elements needed to render and process the form:

 * A reference to the actual form content type that contains the fields used
 * A reference to the domain content type which is used to secure access to your form
 * A reference to the submission content type which is used to submit the form data where you need it
 * A theme field that contains the Symfony theme you want to use for your form

 All these field names are configurable, except for the domain field.

### Form Field

```yaml
instance:
    form-field: form
```

The name of the field in the `form_instance` content type that references the form definition content type.
The referenced `form` content type contains all fields of the form, in the correct order, with necessary validations. (Inlcuding the submit button!)

### Theme Field

```yaml
instance:
    theme-field: theme_template
```

The name of the field in the `form_instance` content type that contains the value of the Symfony template to use for rendering of the form.

### Submission Field

```yaml
instance:
    submission-field: submissions
```

The name of the field in the `form_instance` content type that references the submission content type. The submission contains the endpoint and body of information to send the data with.

### Type Form Field & Type Form Markup

```yaml
instance:
    type-form-field: form_structure_field
    type-form-markup: form_structure_markup
    type-form-subform: form_structure
```

We need to be able to differentiate real form fields from simple text markup and from complex subforms. We do that based on the content type used when rendering the field/markup/subform. Using the above config you can change the names of the content types you use for each.
