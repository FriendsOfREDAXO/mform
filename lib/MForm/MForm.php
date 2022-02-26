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
     * @var string
     */
    private $theme;

    /**
     * @var bool
     */
    private $debug;

    /**
     * mform constructor.
     * @param string|null $theme
     * @param bool $debug
     */
    function __construct(string $theme = null, bool $debug = false)
    {
        $this->theme = $theme;
        $this->debug = $debug;

        parent::__construct();
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function show(): string
    {
        // mfrom count++
        try {
            rex_set_session('mform_count', rex_session('mform_count') + 1);
            $parser = new MFormParser();
            return $parser->parse($this->getItems(), $this->theme, $this->debug);
        } catch (rex_exception $e) {
            rex_logger::logException($e);
            return rex_view::error($e->getMessage());
        }
    }

    /**
     * @param string|null $theme
     * @param bool $debug
     * @return MForm
     * @author Joachim Doerr
     */
    public static function factory(string $theme = null, bool $debug = false): MForm
    {
        $class = static::getFactoryClass();
        return new $class($theme, $debug);
    }
}
