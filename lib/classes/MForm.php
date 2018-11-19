<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MForm extends MFormElements
{
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
     * @param string $template
     * @param bool $debug
     */
    function __construct($template = null, $debug = false)
    {
        $this->theme = $template;
        $this->debug = $debug;

        parent::__construct();
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function show()
    {
        // mfrom count++
        rex_set_session('mform_count', rex_session('mform_count') + 1);

        // init obj
        $parser = new MFormParser();
        // parse elements
        return $parser->parse($this->getItems(), $this->theme, $this->debug);
    }
}
