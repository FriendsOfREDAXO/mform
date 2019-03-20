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
     * @param bool $fill
     */
    function __construct($template = null, $debug = false, $fill = true)
    {
        $this->theme = $template;
        $this->debug = $debug;

        parent::__construct($fill);
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function show()
    {
        $_SESSION['mform_count']++;
        // init obj
        $parser = new MFormParser();
        // parse elements
        return $parser->parse($this->getItems(), $this->theme, $this->debug);
    }
}
