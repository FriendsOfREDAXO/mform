<?php
/**
 * Class MForm
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.6.x
 * @version 3.0.0
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
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
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
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
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
        $parser = new ParseMForm();

        // debug output
        if ($this->debug === true) {
            echo PHP_EOL . '<pre>' . PHP_EOL;
            print_r($this->getArray());
            echo PHP_EOL . '</pre>' . PHP_EOL;
        }
        return $parser->parse($this->getArray(), $this->template);
    }

    /**
     * @return string
     * @deprecated
     * @author Joachim Doerr
     */
    public function show_mform() {
        return $this->show();
    }
}
