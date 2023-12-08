# Supported validations

## Overview

### HTML5 validations
* [Email](#email)
* [FileMimeTypes](#filemimetypes)
* [Max](#max)
* [MaxLength](#max-length)
* [Min](#min)
* [MinLength](#min-length)
* [Required](#required)

### Custom validations
* [CompanyNumber](#companynumber)
* [CompanyNumberMultiple](#companynumbermultiple)
* [MaxFileSize](#max-file-size)
* [MaxLengthCounter](#max-length-counter)
* [NissInsz](#niss-insz)
* [OnssRsz](#onss-rsz)
* [Phone](#phone)
* [RequiredWithout](#required-without)
* [RequiredIf](#required-if)
* [Expression](#expression)

## Validations

### CompanyNumber
Validate that the input string is a valid company registration number in Belgium.
This validation is 'forgiving', meaning that all non valid input characters are filtered away before validation. This allows the end user to input his number in the format he likes.

### CompanyNumberMultiple
Validate that the input string contains only valid company registration numbers in Belgium. 
This validation is 'forgiving', meaning that all non valid input characters are filtered away before validation. This allows the end user to input his number in the format he likes.

### Email
Validate email input as per HTML5 standard definition.

### FileMimeTypes
Define the MIME type(s) accepted in the file field. You can add more than 1 type separated by comma (,).

### Max
Define a maximum value that can be used as input of the associated field.

### Max File Size
Define the maximum allowed size for uploaded files. Check the [Symfony docs](https://symfony.com/doc/current/reference/constraints/File.html#maxsize) for available formats. 

### Max Length
Define a maximum number of characters that can be used in the input of the associated field.

### Min
Define a minimum value that can be used as input of the associated field.

### Min Length
Define a minimum number of characters that should be used in the input of the associated field.

### Required
Determine that a field is required.

### Max Length Counter
Define a maximum number of characters that can be used in the input of the associated field.
Use this variant of the Max Lenght validation if you want to automatically show a counter of the remaining number of characters available for the end user.

### NISS INSZ
Validate that the given number is a valid Belgium NISS (fr) / INSZ (nl) number. Implementation details are documented in the source code.
This validation is 'forgiving', meaning that all non valid input characters are filtered away before validation. This allows the end user to input his number in the format he likes.

### ONSS RSZ
Validate that the given number is a valid Belgium ONSS (fr) / RSZ (nl) number. Implementation details are documented in the source code.
This validation is 'forgiving', meaning that all non valid input characters are filtered away before validation. This allows the end user to input his number in the format he likes.

### Phone
Validate that the input is a valid phone number based on Belgium fixed and mobile lines.

### Required without
The field under validation must be present and not empty only if the "other field" is NOT present. The field name of the "other field" (technical key) needs to be added in the default value field.

### Required if
The field under validation will be required if the expression in the value evaluates to true. 

You can use the full formData as data in the in expression.
Example value: data["otherFieldX"] === "true" and data["otherFieldY"] > 1

More documentation: 
    - [Symfony Expression language](https://symfony.com/doc/current/components/expression_language/syntax.html#component-expression-arrays)

Finally if this validation is NOT working, please check the error logs.

### Expression
The field under validation will be evaluated against the expression in the value, and will pass if it evaluates to true.

The expression must explicitly return true or false.
You can use the full formData as data in the in expression.
Example value: 1 == (data["myField"] matches "/^[a-zA-Z0-9]{10}$/")

More documentation: 
    - [Symfony Expression language](https://symfony.com/doc/current/components/expression_language/syntax.html)

Finally if this validation is NOT working, please check the error logs.
