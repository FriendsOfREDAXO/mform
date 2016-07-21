<?php
/**
 * Author: Joachim Doerr
 * Date: 16.07.16
 * Time: 11:52
 */

class MFormOptionHandler
{
    /**
     * set option to item
     * @param MFormItem $item
     * @param mixed $value
     * @param mixed $key
     * @author Joachim Doerr
     */
    static public function setOption(MFormItem $item, $value, $key)
    {
        // add option to options array
        $item->options[$key] = MFormClang::getClangValue($value);
    }

    /**
     * set opt group to item
     * @param MFormItem $item
     * @param string $label
     * @param mixed $options
     * @author Joachim Doerr
     */
    static public function setOptGroup(MFormItem $item, $label, $options)
    {
        $option = array();
        foreach ($options as $key => $value) {
            // add option to option array
            $option[$key] = MFormClang::getClangValue($value);
        }
        // add option array to options array
        $item->options[$label] = $option;
    }

    /**
     * set option array to item
     * @param MFormItem $item
     * @param array $options
     * @author Joachim Doerr
     */
    static public function setOptions(MFormItem $item, $options)
    {
        // if options an array
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                if(is_array($value)) { // and is value an array
                    // add opt group by setOptGroup method
                    self::setOptGroup($item, $key, $value);
                } else {
                    // add default option by setOption method
                    self::setOption($item, $value, $key);
                }
            }
        }
    }

    /**
     * set options form sql table as array to item
     * @param MFormItem $item
     * @param string $query
     * @throws rex_sql_exception
     * @author Joachim Doerr
     */
    static public function setSqlOptions(MFormItem $item, $query)
    {
        $sql = rex_sql::factory();
        $sql->setQuery($query);
        while ($sql->hasNext()) {
            self::setOption($item, $sql->getValue('name'), $sql->getValue('id'));
            $sql->next();
        }
    }
}
