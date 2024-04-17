<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

use MForm\DTO\MFormInputsConfig;
use MForm\Inputs\MFormInputsInterface;
use MForm\MFormElements;
use MForm\Parser\MFormParser;

class MForm extends MFormElements
{
    use rex_factory_trait;

    private string $theme;
    private bool $debug;

    function __construct(?string $theme = null, bool $debug = false)
    {
        $this->setTheme((!is_null($theme)) ? $theme : '');
        $this->setDebug($debug);
        parent::__construct();
    }

    public function show(): string
    {
        // MForm count++
        try {
            rex_set_session('mform_count', rex_session('mform_count') + 1);
            $parser = new MFormParser();
            return $parser->parse($this->getItems(), ((!empty($this->theme)) ? $this->theme : null), $this->debug);
        } catch (rex_exception $e) {
            rex_logger::logException($e);
            return rex_view::error($e->getMessage());
        }
    }

    public static function factory($debug = false): MForm
    {
        $class = static::getFactoryClass();
        return new $class(null, $debug);
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
}
