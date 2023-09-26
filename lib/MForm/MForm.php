<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

use MForm\MFormElements;
use MForm\Parser\MFormParser;

class MForm extends MFormElements
{
    use rex_factory_trait;

    /** @var string|null */
    private string $theme;

    private bool $inline;

    private bool $debug;

    /**
     * mform constructor.
     */
    public function __construct(?string $theme = null, bool $debug = false, bool $inline = false)
    {
        $this->theme = (null !== $theme) ? $theme : '';
        $this->debug = $debug;
        $this->inline = $inline;
        parent::__construct();
    }

    /**
     * @author Joachim Doerr
     */
    public function show(): string
    {
        // MForm count++
        try {
            rex_set_session('mform_count', rex_session('mform_count', 'int', 0) + 1);
            $parser = new MFormParser();
            return $parser->parse($this->getItems(), (!empty($this->theme)) ? $this->theme : null, $this->debug, $this->inline);
        } catch (rex_exception $e) {
            rex_logger::logException($e);
            return rex_view::error($e->getMessage());
        }
    }

    /**
     * @author Joachim Doerr
     */
    public static function factory(?string $theme = null, bool $debug = false, bool $inline = false): self
    {
        $class = static::getFactoryClass();
        return new $class($theme, $debug, $inline);
    }

    /**
     * @param string|null $theme
     * @return $this
     * @author Joachim Doerr
     */
    public function setTheme(string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    protected function setInline(bool $inline): self
    {
        $this->inline = $inline;
        return $this;
    }
}
