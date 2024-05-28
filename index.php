<?php

include __DIR__ . '/vendor/autoload.php';

langdonglei\Think::validate([
    'name' => 'required|max:10',
    'age'  => 'required|numeric',
]);

