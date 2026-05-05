<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MFormTemplate;

use FriendsOfRedaxo\MForm;
use rex_exception;

final class TemplateRegistry
{
    /**
     * @var array<string, class-string<TemplateInterface>>
     */
    private static array $templates = [];

    /**
     * @param class-string<TemplateInterface> $templateClass
     */
    public static function register(string $key, string $templateClass): void
    {
        if (!is_subclass_of($templateClass, TemplateInterface::class)) {
            throw new rex_exception(sprintf(
                'MForm template class "%s" must implement %s',
                $templateClass,
                TemplateInterface::class,
            ));
        }

        self::$templates[$key] = $templateClass;
    }

    public static function unregister(string $key): void
    {
        unset(self::$templates[$key]);
    }

    public static function has(string $key): bool
    {
        return isset(self::$templates[$key]);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function apply(MForm $form, string $key, array $context = []): MForm
    {
        $templateClass = self::$templates[$key] ?? null;
        if (null === $templateClass) {
            return $form;
        }

        $template = new $templateClass();

        return $template->apply($form, $context);
    }
}
