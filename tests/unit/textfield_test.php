<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class MForm_Textfield_Test extends TestCase
{
    public function testTextfield()
    {
        $mform = MForm::factory();
        rex_login::startSession();
        $mform->addTextField('1.var');
        $actual = $mform->show() ?? '';
        $expected = '<div class="mform form-horizontal"><div class="form-group "><div class="col-sm-2 control-label"><label for="rv1_1_var"></label></div><div class="col-sm-10"><input id="rv1_1_var" type="text" name="REX_INPUT_VALUE[1][var]" value="" class="form-control " ></div></div></div>';
        static::assertEquals($expected, $actual, 'MForm::show() should return a propper html form.');
    }
}
