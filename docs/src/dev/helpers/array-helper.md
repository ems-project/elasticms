# Array Helper

## Find
Find a value recursively inside an array.

```php
use EMS\Helpers\ArrayHelper\ArrayHelper;

ArrayHelper::find('example', ['example' => 'ok']); // returns array("ok")
ArrayHelper::find('example', [ 'child' => ['example' => 1]]); // returns array(1)
ArrayHelper::find('example', ['example' => null]); // returns array(null)
ArrayHelper::find('example', []); // returns array()

ArrayHelper::findString('example', ['example' => 'ok']); // returns 'ok'
ArrayHelper::findInteger('example', [ 'child' => ['example' => 1]]); // returns 1
ArrayHelper::findDateTime('example', [ 'example' => '19/02/1989' ]); // returns \DateTimeInterface
```

## Map
Apply callback function recursively on every value.
The callback will also be called for properties with arrays, before calling child properties.

```php
use EMS\Helpers\ArrayHelper\ArrayHelper;

ArrayHelper::map([1, 2, 3], fn ($v) => $v + 1); // returns array(2, 3, 4)
ArrayHelper::map(['test' => 'test'], fn ($v, $p) => $p === 'test' ? 'TEST' : null); // returns array("TEST")
```
