<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MFormTemplate;

use FriendsOfRedaxo\MForm;

interface TemplateInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function apply(MForm $form, array $context = []): MForm;
}
