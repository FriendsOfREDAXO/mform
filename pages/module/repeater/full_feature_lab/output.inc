<?php

$values = [];
for ($i = 1; $i <= 20; $i++) {
    $values[$i] = 'REX_VALUE[' . $i . ']';
}

echo '<pre>' . htmlspecialchars(print_r($values, true)) . '</pre>';
