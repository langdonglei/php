<?php
header("access-control-allow-origin:*");
header("access-control-allow-methods:*");
header("access-control-allow-headers:*");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    return;
}