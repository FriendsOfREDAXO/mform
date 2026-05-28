<?php

use FriendsOfRedaxo\MForm\Output\MFormContentBlocksOutput;

$framework = 'bootstrap5';
if (is_string('REX_VALUE[id=2]') && '' !== trim('REX_VALUE[id=2]')) {
    $framework = trim('REX_VALUE[id=2]');
}

$output = MFormContentBlocksOutput::from(1);

if ('uikit3' === $framework) {
    echo $output->renderUIKit3();
} elseif ('bulma' === $framework) {
    echo $output->renderBulma();
} else {
    echo $output->renderBootstrap5();
}
