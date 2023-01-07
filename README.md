# PHP Weak Reference Array.

An ArrayAccess implement let you can storage a weak  reference object in array by any key type.

This like an temporary cache, help you to reuse same object when it's still use in other place, and will auto unset the item when all reference is destruct.

## Install

Require php version >= 8.0.0

```sh
composer require pader/weak-array
```

## Simple example:

```php
$hello = new stdClass;
$hello->value = 'hello';
$world = new stdClass;
$world->value = 'world';

$arr = new WeakArray();
$arr[] = $hello;
$arr['foo'] = $world;

var_dump($arr[0]);
var_dump($arr['foo']);

unset($hello);

var_dump($arr[0]); //Should be null
var_dump($arr['foo']);
```
