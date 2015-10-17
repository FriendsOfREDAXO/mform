<?php
/**
 * Class AbstractMForm
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

abstract class AbstractMForm
{
    /**
     * @var string
     */
    private $output;

    /**
     * @var null|array
     */
    private $attributes = NULL;

    /**
     * @var null
     */
    private $options = NULL;

    /**
     * @var null
     */
    private $parameter = NULL;

    /**
     * @var array
     */
    private $elements = array();

    /**
     * @var null|array
     */
    private $result = NULL;

    /**
     * @var null|integer|string
     */
    private $id = NULL;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var null|array
     */
    private $validations = NULL;

    /**
     * generate element array - add fields
     * @param string $typ
     * @param integer|float $id
     * @param null|string $value
     * @param array $attributes
     * @param array $options
     * @param array $parameter
     * @param null $catId
     * @param array $validation
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addElement($typ, $id = NULL, $value = NULL, $attributes = array(), $options = array(), $parameter = array(), $catId = NULL, $validation = array(), $defaultValue = NULL)
    {
        $this->id = $this->count++;
        $subId = false;
        $mode = rex_request('function', 'string');

        if (is_array($splitId = explode('.', str_replace(',', '.', $id))) === true) {
            $id = $splitId[0];

            if (sizeof($splitId) > 1) {
                $subId = $splitId[1];
            }
            if (method_exists('rex_var', 'toArray') === false) {
                $subId = '';
            }
        }

        if (is_array($this->result) === false && $mode == 'edit') {
            $this->getRexVars();

//            echo '<pre>';
//            print_r($this->arrResult);
//            echo '</pre>';
        }

        if ($value === NULL) {
            if (is_array($this->result) === true) {
                switch ($typ) {
                    case 'linklist':
                        $value = $this->result['linklist'][$id];
                        break;
                    case 'medialist':
                        $value = $this->result['filelist'][$id];
                        break;
                    case 'link':
                        $value = $this->result['link'][$id];
                        break;
                    case 'media':
                        $value = $this->result['file'][$id];
                        break;
                    default:
                        $value = $this->result['value'][$id];
                        if (is_array($value) === true) {
                            $value = $this->result['value'][$id][$subId];
                        }
                        break;
                }
            }
        } else {
            $value = $this->getLangData($value);
        }

        if ($defaultValue != NULL) {
            $defaultValue = $this->getLangData($defaultValue);
        }

        $this->elements[$this->id] = array(
            'type' => $typ,
            'id' => $this->id,
            'var-id' => $id,
            'sub-var-id' => $subId,
            'value' => $value,
            'default-value' => $defaultValue,
            'mode' => $mode,
            'cat-id' => (is_numeric($catId) === true) ? $catId : 0,
            'size' => '',
            'attributes' => array(),
            'multi' => '',
            'validation' => array()
        );

        // unset attributes
        $this->attributes = NULL;

        if (sizeof($attributes) > 0) {
            $this->setAttributes($attributes);
        }

        // unset options
        $this->options = NULL;

        if (sizeof($options) > 0) {
            $this->addOptions($options);
        }

        // unset parameters
        $this->parameter = NULL;

        if (sizeof($parameter) > 0) {
            $this->setParameters($parameter);
        }

        // unset validations
        $this->validations = NULL;

        if (sizeof($validation) > 0) {
            $this->setValidations($validation);
        }

        return $this;
    }

    /**
     * @param null|string $value
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addHtml($value)
    {
        return $this->addElement('html', NULL, $value);
    }

    /**
     * @param null|string $value
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addHeadline($value)
    {
        return $this->addElement('headline', NULL, $value);
    }

    /**
     * @param null|string $value
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addDescription($value)
    {
        return $this->addElement('description', NULL, $value);
    }

    /**
     * @param null|string $value
     * @param array $attributes
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addFieldset($value, $attributes = array())
    {
        return $this->addElement('fieldset', NULL, $value, $attributes);
    }

    /**
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function closeFieldset()
    {
        return $this->addElement('close-fieldset', NULL);
    }

    /**
     * @param null $callable
     * @param array $parameter
     * @author Joachim Doerr
     */
    public function callback($callable = NULL, $parameter = array())
    {
        if ((is_string($callable) === true or is_array($callable) === true) && is_callable($callable, true) === true) {
            $intId = $this->count++;
            $this->elements[$intId] = array(
                'type' => 'callback',
                'id' => $intId,
                'callable' => $callable,
                'parameter' => $parameter
            );
        }
    }

    /**
     * @param string $typ
     * @param integer|float $id
     * @param null|string $value
     * @param array $attributes
     * @param array $validations
     * @param null $defaultValue
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addInputField($typ, $id, $value = NULL, $attributes = array(), $validations = array(), $defaultValue = NULL)
    {
        return $this->addElement($typ, $id, $value, $attributes, NULL, NULL, NULL, $validations, $defaultValue);
    }

    /**
     * @param $id
     * @param null|string $value
     * @param array $attributes
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addHiddenField($id, $value = NULL, $attributes = array())
    {
        return $this->addInputField('hidden', $id, $value, $attributes);
    }

    /**
     * @param integer|float $id
     * @param array $attributes
     * @param array $validations
     * @param null $defaultValue
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addTextField($id, $attributes = array(), $validations = array(), $defaultValue = NULL)
    {
        return $this->addInputField('text', $id, NULL, $attributes, $validations, $defaultValue);
    }

    /**
     * @param integer|float $id
     * @param array $attributes
     * @param array $validations
     * @param null $defaultValue
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addTextAreaField($id, $attributes = array(), $validations = array(), $defaultValue = NULL)
    {
        return $this->addInputField('textarea', $id, NULL, $attributes, $validations, $defaultValue);
    }

    /**
     * @param $id
     * @param null $value
     * @param array $attributes
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addTextReadOnlyField($id, $value = NULL, $attributes = array())
    {
        return $this->addInputField('text-readonly', $id, $value, $attributes);
    }

    /**
     * @param $id
     * @param null $value
     * @param array $attributes
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addTextAreaReadOnlyField($id, $value = NULL, $attributes = array())
    {
        return $this->addInputField('area-readonly', $id, $value, $attributes);
    }

    /**
     * add special link feld
     * @param $id
     * @param array $attributes
     * @param array $validations
     * @param null $defaultValue
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addCustomLinkField($id, $attributes = array(), $validations = array(), $defaultValue = NULL)
    {
        return $this->addElement('custom-link', $id, NULL, $attributes, NULL, NULL, NULL, $validations, $defaultValue);
    }

    /**
     * add select fields
     * @param $typ
     * @param $id
     * @param array $attributes
     * @param array $options
     * @param null $defaultValue
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addOptionField($typ, $id, $attributes = array(), $options = array(), $defaultValue = NULL)
    {
        return $this->addElement($typ, $id, NULL, $attributes, $options, NULL, NULL, array(), $defaultValue);
    }

    /**
     * @param $id
     * @param array $options
     * @param array $attributes
     * @param int $size
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addSelectField($id, $options = array(), $attributes = array(), $size = 1, $defaultValue = NULL)
    {
        $this->addOptionField('select', $id, $attributes, $options, $defaultValue);
        $this->setSize($size);
        return $this;
    }

    /**
     * @param $id
     * @param array $options
     * @param array $attributes
     * @param int $size
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addMultiSelectField($id, $options = array(), $attributes = array(), $size = 3, $defaultValue = NULL)
    {
        $this->addOptionField('multiselect', $id, $attributes, $options, $defaultValue);
        $this->setMultiple(true);
        $this->setSize($size);
        return $this;
    }

    /**
     * add checkboxes
     * @param $id
     * @param array $options
     * @param array $attributes
     * @param null $defaultValue
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addCheckboxField($id, $options = array(), $attributes = array(), $defaultValue = NULL)
    {
        return $this->addOptionField('checkbox', $id, $attributes, $options, $defaultValue);
    }

    /**
     * add radiobutton
     * @param $id
     * @param array $options
     * @param array $attributes
     * @param null $defaultValue
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addRadioField($id, $options = array(), $attributes = array(), $defaultValue = NULL)
    {
        return $this->addOptionField('radiobutton', $id, $attributes, $options, $defaultValue);
    }

    /**
     * add rex link fields
     * @param $id
     * @param array $parameter
     * @param null $catId
     * @param array $attributes
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addLinkField($id, $parameter = array(), $catId = NULL, $attributes = array())
    {
        return $this->addElement('link', $id, NULL, $attributes, array(), $parameter, $catId);
    }

    /**
     * add rex link list fields
     * @param $id
     * @param array $parameter
     * @param null $catId
     * @param array $attributes
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addLinklistField($id, $parameter = array(), $catId = NULL, $attributes = array())
    {
        return $this->addElement('linklist', $id, NULL, $attributes, array(), $parameter, $catId);
    }

    /**
     * add rex media field
     * @param $id
     * @param array $parameter
     * @param null $catId
     * @param array $attributes
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addMediaField($id, $parameter = array(), $catId = NULL, $attributes = array())
    {
        return $this->addElement('media', $id, NULL, $attributes, array(), $parameter, $catId);
    }

    /**
     * add rex media list field
     * @param $id
     * @param array $parameter
     * @param null $catId
     * @param array $attributes
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addMedialistField($id, $parameter = array(), $catId = NULL, $attributes = array())
    {
        return $this->addElement('medialist', $id, NULL, $attributes, array(), $parameter, $catId);
    }

    /**
     * @param $label
     * @author Joachim Doerr
     */
    public function setLabel($label)
    {
        $this->elements[$this->id]['label'] = $this->getLangData($label);
    }

    /**
     * @param $name
     * @param $value
     * @author Joachim Doerr
     */
    public function setAttribute($name, $value)
    {
        switch ($name) {
            case 'label':
                $this->setLabel($value);
                break;
            case 'size':
                $this->setSize($value);
                break;
            case 'validation':
                if (is_array($value)) {
                    $arrValidation = $value;
                    $this->setValidations($arrValidation);
                }
                break;
            case 'default-value':
                $this->setDefaultValue($value);
                break;
            default:
                $this->attributes[$name] = $value;
                $this->elements[$this->id]['attributes'] = $this->attributes;
                break;
        }
    }

    /**
     * @param $attributes
     * @author Joachim Doerr
     */
    public function setAttributes($attributes)
    {
        $this->attributes = array();
        if (is_array($attributes)) {
            foreach ($attributes as $strName => $strValue) {
                $this->setAttribute($strName, $strValue);
            }
        }
    }

    /**
     * add default validation
     * @param $key
     * @param $value
     * @author Joachim Doerr
     */
    public function setValidation($key, $value)
    {
        switch ($key) {
            case 'empty':
                $this->setAttribute('data-required', 'true');
                break;
            case 'integer':
                $this->setAttribute('data-type', 'digits');
                break;
            case 'float':
                $this->setAttribute('data-type', 'number');
                break;
            case 'alphanum':
                $this->setAttribute('data-type', 'alphanum');
                break;
            case 'dateIso':
                $this->setAttribute('data-type', 'dateIso');
                break;
            case 'compare':
            case 'email':
                $this->setAttribute('data-type', 'email');
                break;
            case 'minlength':
                $this->setAttribute('data-minlength', $value);
                break;
            case 'maxlength':
                $this->setAttribute('data-maxlength', $value);
                break;
            case 'min':
                $this->setAttribute('data-min', $value);
                break;
            case 'max':
                $this->setAttribute('data-max', $value);
                break;
            case 'url':
                $this->setAttribute('data-type', 'url');
                break;
            case 'regexp':
                $this->setAttribute('data-regexp', $value);
                break;
            case 'mincheck':
                $this->setAttribute('data-mincheck', $value);
                break;
            case 'maxcheck':
                $this->setAttribute('data-maxcheck', $value);
                break;
            case 'custom':
                $this->validations[$key] = $value;
                $this->elements[$this->id]['validation'] = $this->validations;
                break;
        }
    }

    /**
     * @param $validations
     * @author Joachim Doerr
     */
    public function setValidations($validations)
    {
        $this->validations = array();
        foreach ($validations as $key => $value) {
            if (is_numeric($key) === true) {
                $this->setValidation($value, '');
            } else {
                $this->setValidation($key, $value);
            }
        }
    }

    /*
    add custom validation
    */
    public function setCustomValidation($arrCustomValidation)
    {
    }

    public function setCustomValidations($arrCustomValidations)
    {
    }

    /**
     * @param $value
     * @author Joachim Doerr
     */
    public function setDefaultValue($value)
    {
        $this->elements[$this->id]['default-value'] = $this->getLangData($value);
    }

    /**
     * @param $value
     * @param $key
     * @author Joachim Doerr
     */
    public function addOption($value, $key)
    {
        $this->options[$key] = $this->getLangData($value);
        $this->elements[$this->id]['options'] = $this->options;
    }

    /**
     * @param $options
     * @author Joachim Doerr
     */
    public function addOptions($options)
    {
        $this->options = array();
        foreach ($options as $intKey => $strValue) {
            $this->addOption($strValue, $intKey);
        }
    }

    /**
     * @param $query
     * @author Joachim Doerr
     */
    public function addSqlOptions($query)
    {
        $sql = rex_sql::factory();
        $sql->setQuery($query);
        while ($sql->hasNext()) {
            $this->addOption($sql->getValue('name'), $sql->getValue('id'));
            $sql->next();
        }
    }

    /**
     * @param $multiple
     * @author Joachim Doerr
     */
    public function setMultiple($multiple)
    {
        if ($multiple === true) {
            $this->elements[$this->id]['multi'] = true;
        }
    }

    /**
     * @param $size
     * @author Joachim Doerr
     */
    public function setSize($size)
    {
        if ((is_numeric($size) === true && $size > 0) or $size == 'full') {
            $this->elements[$this->id]['size'] = $size;
        }
    }

    /**
     * set category and parameter
     * @param $catId
     * @author Joachim Doerr
     */
    public function setCategory($catId)
    {
        if ($catId > 0) {
            $this->elements[$this->id]['cat-id'] = $catId;
        }
    }

    /**
     * @param $name
     * @param $value
     * @author Joachim Doerr
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'category':
                $this->setCategory($value);
                break;

            case 'label':
                $this->setLabel($value);
                break;

            default:
                $this->parameter[$name] = $value;
                $this->elements[$this->id]['parameter'] = $this->parameter;
                break;
        }
    }

    /**
     * @param $parameter
     * @author Joachim Doerr
     */
    public function setParameters($parameter)
    {
        $this->parameter = array();
        foreach ($parameter as $name => $value) {
            $this->setParameter($name, $value);
        }
    }

    /**
     * use user lang
     * @param $languageData
     * @return mixed|string
     * @author Joachim Doerr
     */
    private function getLangData($languageData)
    {
        if (is_array($languageData) === true) {
            $langData = '';

            foreach ($languageData as $key => $value) {
                if ($key == rex::getUser()->getLanguage() or $key . '_utf8' == rex::getUser()->getLanguage()) {
                    $langData = $value;
                }
            }
            if ($langData == '') {
                $langData = reset($languageData);
            }
        } else {
            $langData = $languageData;
        }
        return $langData;
    }

    /**
     * get rex var
     * @return array|null
     * @author Joachim Doerr
     */
    private function getRexVars()
    {
        $sliceId = rex_request('slice_id', 'int', false);

        if ($sliceId != false) {
            $table = rex::getTablePrefix() . 'article_slice';
            $fields = '*';
            $where = 'id="' . $_REQUEST['slice_id'] . '"';

            $sql = rex_sql::factory();
            $query = '
                SELECT ' . $fields . '
                FROM ' . $table . '
                WHERE ' . $where;

            $sql->setQuery($query);
            $rows = $sql->getRows();

            if ($rows > 0) {
                $this->result = array();

                for ($i = 1; $i <= 20; $i++) {
                    $this->result['value'][$i] = $sql->getValue('value' . $i);

                    if ($i <= 10) {
                        $this->result['filelist'][$i] = $sql->getValue('medialist' . $i);
                        $this->result['linklist'][$i] = $sql->getValue('linklist' . $i);
                        $this->result['file'][$i] = $sql->getValue('media' . $i);
                        $this->result['link'][$i] = $sql->getValue('link' . $i);
                    }

                    $result = json_decode(htmlspecialchars_decode($this->result['value'][$i]),true);

                    if (is_array($result)) {
                        $this->result['value'][$i] = $result;
                    }
                }
            }
        }

        return $this->result;
    }

    /**
     * check serialize
     * @param $string
     * @return bool
     * @author Joachim Doerr
     */
    public static function isSerial($string)
    {
        return (@unserialize($string) !== false);
    }

    /**
     * generate Output
     * @return array|string
     * @author Joachim Doerr
     */
    protected function getArray()
    {
        $this->output = $this->elements;
        return $this->output;
    }
}
