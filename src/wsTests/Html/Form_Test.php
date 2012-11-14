<?php
namespace wsTests\Html;
require( __DIR__ . '/../../autoloader.php' );
use wsCore\Html\Form as Form;

class Form_Test extends \PHPUnit_Framework_TestCase
{
    /** @var Form */
    public $form;
    public function setUp()
    {
        $this->form = new Form();
    }
    // +----------------------------------------------------------------------+
    function test_form_returns_new_object()
    {
        $form = $this->form->input( 'text', 'user_name', 'taro-san' );
        $this->assertEquals( get_class( $this->form ), get_class( $form ) );
        $this->assertNotEquals( $this->form, $form );

        $form = $this->form->textArea( 'user_memo', 'this is memo' );
        $this->assertEquals( get_class( $this->form ), get_class( $form ) );
        $this->assertNotEquals( $this->form, $form );

        $form = $this->form->check( 'user_OK', 'YES' );
        $this->assertEquals( get_class( $this->form ), get_class( $form ) );
        $this->assertNotEquals( $this->form, $form );

        $form = $this->form->radio( 'user_OK', 'YES' );
        $this->assertEquals( get_class( $this->form ), get_class( $form ) );
        $this->assertNotEquals( $this->form, $form );

        $form = $this->form->radioLabel( 'OK', 'YES', 'really?' );
        $this->assertEquals( get_class( $this->form ), get_class( $form ) );
        $this->assertNotEquals( $this->form, $form );

        $form = $this->form->checkLabel( 'OK', 'YES', 'really?' );
        $this->assertEquals( get_class( $this->form ), get_class( $form ) );
        $this->assertNotEquals( $this->form, $form );

        $ages = array(
            array( '10', 'teenage' ),
            array( '20', 'twenties' ),
            array( '30', 'thirtish' ),
        );
        $form = $this->form->radioBox( 'user_age', $ages, '20' );
        $this->assertEquals( get_class( $this->form ), get_class( $form ) );
        $this->assertNotEquals( $this->form, $form );

        $form = $this->form->checkBox( 'user_age', $ages, '20' );
        $this->assertEquals( get_class( $this->form ), get_class( $form ) );
        $this->assertNotEquals( $this->form, $form );

        $lang = array(
            array( 'eng', 'english' ),
            array( 'ger', 'german' ),
            array( 'fra', 'french' ),
        );
        $form = $this->form->select( 'lang', $lang, 'ger' );
        $this->assertEquals( get_class( $this->form ), get_class( $form ) );
        $this->assertNotEquals( $this->form, $form );

    }
    function test_list_box()
    {
        $list = array( 'item1', 'more', 'another' );
        $form = (string) $this->form->listBox( $list );
        $this->assertEquals( '<div class="formListBox"><nl>
  <li>item1</li>
  <li>more</li>
  <li>another</li></nl>
</div>' . "\n", $form );
    }
    function test_select_with_group()
    {
        $lang = array(
            array( 'eng', 'english' ),
            array( 'ger', 'german', 'europe' ),
            array( 'fra', 'french', 'europe' ),
            array( 'spa', 'spanish', 'europe' ),
            array( 'jpn', 'japanese' ),
            array( 'zhi', 'chinese', 'asia' ),
            array( 'kor', 'korean', 'asia' ),
        );
        $form = (string) $this->form->select( 'lang', $lang, array( 'ger', 'zhi' ), array( 'multiple' => true ) );
        $this->assertEquals( '<select name="lang[]" multiple="_multiple">
  <option value="eng">english</option>
  <optgroup label="europe">
    <option value="ger" selected="selected">german</option>
    <option value="fra">french</option>
    <option value="spa">spanish</option>
  </optgroup>
  <option value="jpn">japanese</option>
  <optgroup label="asia">
    <option value="zhi" selected="selected">chinese</option>
    <option value="kor">korean</option>
  </optgroup>
</select>' . "\n", $form );
    }
    function test_select()
    {
        $lang = array(
            array( 'eng', 'english' ),
            array( 'ger', 'german' ),
            array( 'fra', 'french' ),
        );
        $form = (string) $this->form->select( 'lang', $lang, 'ger' );
        $this->assertEquals( '<select name="lang">
  <option value="eng">english</option>
  <option value="ger" selected="selected">german</option>
  <option value="fra">french</option>
</select>' . "\n", $form );
    }
    function test_check_in_box()
    {
        $ages = array(
            array( '10', 'teenage' ),
            array( '20', 'twenties' ),
            array( '30', 'thirtish' ),
        );
        $form = $this->form->checkBox( 'user_age', $ages, '20' );
        $form = (string) $form;
        $this->assertEquals( '<div class="formListBox"><nl>
  <li><label><input type="checkbox" name="user_age[]" value="10" />
teenage</label>
</li>
  <li><label><input type="checkbox" name="user_age[]" value="20" checked="checked" />
twenties</label>
</li>
  <li><label><input type="checkbox" name="user_age[]" value="30" />
thirtish</label>
</li></nl>
</div>'."\n", $form );    
    }
    function test_radio_in_box()
    {
        $ages = array(
            array( '10', 'teenage' ),
            array( '20', 'twenties' ),
            array( '30', 'thirtish' ),
        );
        $form = (string) $this->form->radioBox( 'user_age', $ages, '20' );
        $this->assertEquals( '<div class="formListBox"><nl>
  <li><label><input type="radio" name="user_age" value="10" />
teenage</label>
</li>
  <li><label><input type="radio" name="user_age" value="20" checked="checked" />
twenties</label>
</li>
  <li><label><input type="radio" name="user_age" value="30" />
thirtish</label>
</li></nl>
</div>'."\n", $form );
    }
    function test_check_in_label()
    {
        $form = (string) $this->form->checkLabel( 'OK', 'YES', 'really?' );
        $this->assertEquals( '<label><input type="checkbox" name="OK[]" value="YES" />' . "\n".
            'really?</label>'."\n", $form );

        // check if radio does NOT have [] in the name. 
        $form = (string) $this->form->radio( 'user_OK', 'YES' );
        $this->assertEquals( '<input type="radio" name="user_OK" value="YES" />'."\n", $form );
    }
    function test_radio_in_label()
    {
        $form = (string) $this->form->radioLabel( 'OK', 'YES', 'really?' );
        $this->assertEquals( '<label><input type="radio" name="OK" value="YES" />' . "\n".
            'really?</label>'."\n", $form );
    }
    function test_check()
    {
        $form = (string) $this->form->check( 'user_OK', 'YES' );
        $this->assertEquals( '<input type="checkbox" name="user_OK[]" value="YES" />'."\n", $form );

        // check if radio does NOT have [] in the name. 
        $form = (string) $this->form->radio( 'user_OK', 'YES' );
        $this->assertEquals( '<input type="radio" name="user_OK" value="YES" />'."\n", $form );
    }
    function test_radio()
    {
        $form = (string) $this->form->radio( 'user_OK', 'YES' );
        $this->assertEquals( '<input type="radio" name="user_OK" value="YES" />'."\n", $form );
    }
    function test_textarea()
    {
        $form = (string) $this->form->textArea( 'user_memo', 'this is memo' );
        $this->assertEquals( '<textarea name="user_memo">this is memo</textarea>'."\n", $form );

        $form = (string) $this->form->textArea( 'user_memo', 'this is memo' )->_ime( 'ON' );
        $this->assertEquals( '<textarea name="user_memo" style="ime-mode:active">this is memo</textarea>'."\n", $form );
        
        $form = (string) $this->form->textArea( 'user_memo', 'this is memo. ' )->contain_( 'more memo' );
        $this->assertEquals( '<textarea name="user_memo">this is memo. more memo</textarea>'."\n", $form );
    }
    function test_input_no_class_is_set()
    {
        $form = (string) $this->form->input( 'text', 'user_name', 'taro-san', array( 'class' => 'myClass', 'ime' => 'ON' ) );
        $this->assertContains( '<input type="text" name="user_name" value="taro-san"', $form );
        $this->assertContains( 'value="taro-san" class="myClass" style="ime-mode:active" />'."\n", $form );

        // no class is set. 
        $form = (string) $this->form->input( 'date', 'user_bdate', '1989-01-01' )->_ime( 'OFF' );
        $this->assertEquals( '<input type="date" name="user_bdate" value="1989-01-01" style="ime-mode:inactive" />'."\n", $form );
    }
    function test_input_form()
    {
        $form = (string) $this->form->input( 'text', 'user_name', 'taro-san' );
        $this->assertEquals( '<input type="text" name="user_name" value="taro-san" />'."\n", $form );
        
        $form = (string) $this->form->input( 'text', 'user_name', 'taro-san', array( 'class' => 'myClass', 'ime' => 'ON' ) );
        $this->assertContains( '<input type="text" name="user_name" value="taro-san"', $form );
        $this->assertContains( 'value="taro-san" class="myClass" style="ime-mode:active" />'."\n", $form );
    }
}
