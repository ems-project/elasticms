# Standards

<!-- TOC -->
* [Standards](#standards)
  * [Accessor](#accessor)
  * [Base64](#base64)
  * [DateTime](#datetime)
  * [Hash](#hash)
  * [Html](#html)
    * [Pretty Print](#pretty-print)
    * [Sanitize](#sanitize)
  * [Json](#json)
  * [Text](#text)
  * [Type](#type)
<!-- TOC -->

## Accessor
Convert a string `field path` to and array `property path`.

```php
use EMS\Helpers\Standard\Accessor;
$path = Accessor::fieldPathToPropertyPath('src.table[0].title'); // [src][table][0][title]
```

## Base64
Encode and decode a string value.

```php
use EMS\Helpers\Standard\Base64;
$encoded = Base64::encode('foobar'); //Zm9vYmFy
$decoded = Base64::decode($encoded) //foobar
```

## DateTime
Create from time or form format, and allways return a \DateTimeInterface or throwing \RuntimeExceptions

```php
<?php
  use EMS\Helpers\Standard\DateTime;
  
  $fromTime = DateTime::create('now');
  $fromFormat = DateTime::createFromFormat('01-01-2023', 'd-m-Y');
  $dateTime = DateTime::create('2018-12-31 13:05:21');
  $atomDate = DateTime::createFromFormat('2021-03-09T09:53:10+0100', \DATE_ATOM);
```

## Hash

## Html
Standard for working with html texts.

```php
use EMS\Helpers\Standard\Html;

$html = new Html('<h1>Test</h1><div>test</div><span></span>');

echo $html->prettyPrint(['drop-empty-elements'])); //<h1>Test</h1><div>test</div>
echo $html->sanitize(['drop_elements' => ['div']])); //<h1>Test</h1><span></span>
echo $html->sanitize(['drop_elements' => ['div']]))->prettyPrint(['drop-empty-elements'])); //<h1>Test</h1>
```

### Pretty Print

Uses [tidy](https://www.php.net/manual/en/book.tidy.php) for cleaning and repairing the html.

```php
$defaultParseOptions = [
    'indent' => true,
    'indent-spaces' => 2,
    'newline' => 'LF',
    'wrap' => 68,
    'hide-comments' => 1,
    'drop-empty-elements' => false,
]
```

### Sanitize

Uses symfony [HTML Sanitizer](https://symfony.com/doc/current/html_sanitizer.html) component.

```php
$defaultSettings = [
    'allow_safe_elements' => true,
    'allow_attributes' => ['class' => '*']
]
```

```php
$settings = [
    'allow_safe_elements' => true, // On true will load W3C valid elements and attributes in `allow_elements`
    'allow_attributes' => [
        'class' => '*',
        'style' => ['span', 'div'] // Allow `style` attribute on span and div elements
    ],
    'allow_elements' => [
        'div' => '*' // allow div elements with any attributes
    ],
    'block_elements' => [ 'a' ], // remove all `a` elements but keep content
    'drop_attributes' => [
        'id' => ['h1', 'h2'] // remove `id` attribute from h1 and h2 elementgs
    ],
    'drop_elements' => [ 'iframe' ], // remove all `iframe` elements and content
    'classes' => [
        'allow' => ['my-class', 'my-second-class', 'new-class'],
        'drop' => ['delete', 'remove'],
        'replace' => ['old-class' => 'new-class'], // IMPORTANT: add new-class in the 'allow' array
    ]
]
```

## Json

> Because php json_encode can return false and json_decode mixed. This common standard will throw runtime exceptions.

```php
<?php
        use EMS\Helpers\Standard\Json;
        $pretty = true;
        $encode = Json::encode(['test' => 'test'], $pretty);
        $decode = Json::decode($encode);
```

## Text

## Type
