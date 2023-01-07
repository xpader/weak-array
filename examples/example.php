<?php

use WeakArray\WeakArray;

require __DIR__.'/../vendor/autoload.php';


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

echo count($arr)."\n";

