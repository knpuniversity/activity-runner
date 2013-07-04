<?php

$array   = array('foo', 'bar');
$literal = 'foo';
$object  = new \stdClass();
$object->foo = 'foo';
$object->baz = 'baz';

return array(
    'arrayy'  => $array,
    'literal' => $literal,
    'object'  => $object,
);
