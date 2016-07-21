<?php
/**
 * Author: Joachim Doerr
 * Date: 15.07.16
 * Time: 22:53
 */

class MFormParameterHandler
{
    /**
     * @param MFormItem $item
     * @param mixed $name
     * @param mixed $value
     * @author Joachim Doerr
     */
    static public function setParameter(MFormItem $item, $name, $value)
    {
        switch ($name) {
            case 'category':
                if ($value > 0)
                    $item->setCatId($value);
                break;
            case 'label':
                $item->setLabel($value);
                break;
            case 'full':
                $item->setFull(true);
                break;
            default:
                $item->parameter[$name] = $value;
                break;
        }
    }

    /**
     * @param MFormItem $item
     * @param array $parameter
     * @author Joachim Doerr
     */
    static public function setParameters(MFormItem $item, $parameter)
    {
        // is parameter an array
        if (is_array($parameter)) {
            foreach ($parameter as $name => $value) {
                // set parameter by setParameter method
                self::setParameter($item, $name, $value);
            }
        }
    }

}
