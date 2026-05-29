<?php

/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo;

use FriendsOfRedaxo\MFormTemplate\TemplateInterface;
use FriendsOfRedaxo\MForm\MFormElements;
use FriendsOfRedaxo\MForm\Parser\MFormParser;
use FriendsOfRedaxo\MFormTemplate\TemplateRegistry;
use rex_exception;
use rex_factory_trait;
use rex_logger;
use rex_view;

class MForm extends MFormElements
{
    use rex_factory_trait;

    private string $theme;
    private bool $debug;

    private bool $showWrapper = true;

    /**
    * Wenn true, werden addMediaField(), addLinkField() und addLinklistField()
    * intern ueber die MForm-Widgets gerendert. Das Speicherformat bleibt
    * identisch (REX_MEDIA / REX_LINK / REX_LINKLIST). Vorteil: kein
    * Reindex-Problem in MBlock, sauberes Clone/Reset-Verhalten,
    * einheitlicher Widget-Stil.
     */
    private static bool $useCustomLinkForClassicWidgets = false;

    /**
    * Schaltet das MForm-Widget-Rendering fuer addMediaField(), addLinkField()
    * und addLinklistField() ein oder aus.
     *
     * Typische Verwendung direkt vor MForm::factory() im Moduleingabe-Code:
     *
     *   MForm::useCustomLinkForClassicWidgets(true);
     *   $mform = MForm::factory()->addMediaField(1, ...)->show();
     *
    * Default: false (klassische Core-Widgets, volle Rueckwaertskompatibilitaet).
     */
    public static function useCustomLinkForClassicWidgets(bool $enable = true): void
    {
        self::$useCustomLinkForClassicWidgets = $enable;
    }

    public static function isUsingCustomLinkForClassicWidgets(): bool
    {
        return self::$useCustomLinkForClassicWidgets;
    }

    public function __construct(?string $theme = null, bool $debug = false)
    {
        $this->setTheme((!is_null($theme)) ? $theme : '');
        $this->setDebug($debug);
        parent::__construct();
    }

    public function show(): string
    {
        // MForm count++
        try {
            rex_set_session('mform_count', (int) rex_session('mform_count', 'int', 0) + 1);
            $parser = new MFormParser();
            return $parser->parse($this->getItems(), (('' !== $this->theme) ? $this->theme : null), $this->showWrapper, $this->debug);
        } catch (rex_exception $e) {
            rex_logger::logException($e);
            return rex_view::error($e->getMessage());
        }
    }

    public static function factory(bool $debug = false): MForm
    {
        $class = static::getFactoryClass();
        return new $class(null, $debug);
    }

    /**
     * Erstellt ein MForm anhand eines Template-Keys.
     *
     * Templates koennen projektseitig ueber MForm::registerTemplate()
     * registriert werden.
     *
     * @param array<string, mixed> $context
     */
    public static function fromTemplate(string $key, array $context = [], bool $debug = false): MForm
    {
        return self::factory($debug)->applyTemplate($key, $context);
    }

    /**
     * Registriert eine Template-Klasse fuer einen Key.
     *
     * @param class-string<TemplateInterface> $templateClass
     */
    public static function registerTemplate(string $key, string $templateClass): void
    {
        TemplateRegistry::register($key, $templateClass);
    }

    /**
     * Entfernt einen registrierten Template-Key.
     */
    public static function unregisterTemplate(string $key): void
    {
        TemplateRegistry::unregister($key);
    }

    /**
     * Prueft, ob ein Template-Key registriert ist.
     */
    public static function hasTemplate(string $key): bool
    {
        return TemplateRegistry::has($key);
    }

    /**
     * Wendet ein registriertes Template auf die aktuelle Form an.
     *
     * @param array<string, mixed> $context
     */
    public function applyTemplate(string $key, array $context = []): self
    {
        /** @var self $resolved */
        $resolved = TemplateRegistry::apply($this, $key, $context);

        return $resolved;
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    public function setShowWrapper(bool $showWrapper): self
    {
        $this->showWrapper = $showWrapper;
        return $this;
    }
}
