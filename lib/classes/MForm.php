<?php
/**
 * Class MForm
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

class MForm extends AbstractMForm
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    private $debug;

    /**
     * mform constructor.
     * @param string $template
     * @param bool $debug
     */
    function __construct($template = 'default', $debug = false)
    {
        $this->template = $template;
        $this->debug = $debug;

        parent::__construct();
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @param boolean $debug
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function show()
    {
        // init obj
        $parser = new MFormParser();
        // parse elements
        return $parser->parse($this->getItems(), $this->template, $this->debug);
    }

    /**
     * @return string
     * @deprecated this method will be removed in v5
     * @author Joachim Doerr
     */
    public function show_mform() {
        return $this->show();
    }
}
