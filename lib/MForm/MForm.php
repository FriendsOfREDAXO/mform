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

    /**
     * @var string|null
     */
    private string $theme;

    /**
     * @var bool
     */
    private bool $inline;

    /**
     * @var bool
     */
    private bool $debug;

    /**
     * mform constructor.
     * @param string|null $theme
     * @param bool $debug
     * @param bool $inline
     */
    function __construct(?string $theme = null, bool $debug = false, bool $inline = false)
    {
        $this->theme = (!is_null($theme)) ? $theme : '';
        $this->debug = $debug;
        $this->inline = $inline;
        parent::__construct();
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function show(): string
    {
        // MForm count++
        try {
            rex_set_session('mform_count', rex_session('mform_count', 'int', 0) + 1);
            $parser = new MFormParser();
            return $parser->parse($this->getItems(), ((!empty($this->theme)) ? $this->theme : null), $this->debug, $this->inline);
        } catch (rex_exception $e) {
            rex_logger::logException($e);
            return rex_view::error($e->getMessage());
        }
    }

    /**
     * @param string|null $theme
     * @param bool $debug
     * @param bool $inline
     * @return MForm
     * @author Joachim Doerr
     */
    public static function factory(string $theme = null, bool $debug = false, bool $inline = false): MForm
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
     * @param bool $debug
     * @return $this
     * @author Joachim Doerr
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param bool $inline
     * @return $this
     * @author Joachim Doerr
     */
    protected function setInline(bool $inline): self
    {
        $this->inline = $inline;
        return $this;
    }
}
