<?php

namespace Zan\Framework\Test\Utilities\Validation;

use Zan\Framework\Utilities\Validation\Validator;

class ValidationValidatorTest extends \TestCase
{
    public function testSometimesWorksOnNestedArrays()
    {
        $v = new Validator(['foo' => ['bar' => ['baz' => '']]], ['foo.bar.baz' => 'sometimes|required']);
        $this->assertFalse($v->passes());
        $this->assertEquals(['foo.bar.baz' => ['Required' => []]], $v->failed());

        $v = new Validator(['foo' => ['bar' => ['baz' => 'nonEmpty']]], ['foo.bar.baz' => 'sometimes|required']);
        $this->assertTrue($v->passes());
    }

    public function testAfterCallbacksAreCalledWithValidatorInstance()
    {
        $v = new Validator(['foo' => 'bar', 'baz' => 'boom'], ['foo' => 'Same:baz']);
        $v->after(function ($validator) {
            $_SERVER['__validator.after.test'] = true;

            // For asserting we can actually work with the instance
            $validator->errors()->add('bar', 'foo');
        });

        $this->assertFalse($v->passes());
        $this->assertTrue($_SERVER['__validator.after.test']);
        $this->assertTrue($v->errors()->has('bar'));

        unset($_SERVER['__validator.after.test']);
    }

    public function testSometimesWorksOnArrays()
    {
        $v = new Validator(['foo' => ['bar', 'baz', 'moo']], ['foo' => 'sometimes|required|between:5,10']);
        $this->assertFalse($v->passes());
        $this->assertNotEmpty($v->failed());

        $v = new Validator(['foo' => ['bar', 'baz', 'moo', 'pew', 'boom']], ['foo' => 'sometimes|required|between:5,10']);
        $this->assertTrue($v->passes());
    }

    public function testHasFailedValidationRules()
    {
        $v = new Validator(['foo' => 'bar', 'baz' => 'boom'], ['foo' => 'Same:baz']);
        $this->assertFalse($v->passes());
        $this->assertEquals(['foo' => ['Same' => ['baz']]], $v->failed());
    }

    public function testFailingOnce()
    {
        $v = new Validator(['foo' => 'bar', 'baz' => 'boom'], ['foo' => 'Bail|Same:baz|In:qux']);
        $this->assertFalse($v->passes());
        $this->assertEquals(['foo' => ['Same' => ['baz']]], $v->failed());
    }

    public function testHasNotFailedValidationRules()
    {
        $v = new Validator(['foo' => 'taylor'], ['name' => 'Confirmed']);
        $this->assertTrue($v->passes());
        $this->assertEmpty($v->failed());
    }

    public function testSometimesCanSkipRequiredRules()
    {
        $v = new Validator([], ['name' => 'sometimes|required']);
        $this->assertTrue($v->passes());
        $this->assertEmpty($v->failed());
    }

    public function testInValidatableRulesReturnsValid()
    {
        $v = new Validator(['foo' => 'taylor'], ['name' => 'Confirmed']);
        $this->assertTrue($v->passes());
    }

    public function testProperLanguageLineIsSet()
    {
        $v = new Validator(['name' => ''], ['name' => 'Required'], ['name.required' => 'required!']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('required!', $v->messages()->first('name'));
    }

    public function testCustomReplacersAreCalled()
    {
        $v = new Validator(['name' => ''], ['name' => 'Required'], ['name.required' => 'foo bar']);
        $v->addReplacer('required', function ($message, $attribute, $rule, $parameters) { return str_replace('bar', 'taylor', $message); });
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('foo taylor', $v->messages()->first('name'));
    }

    public function testAttributeNamesAreReplaced()
    {
        $v = new Validator(['name' => ''], ['name' => 'Required'], ['name.required' => ':attribute is required!']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('name is required!', $v->messages()->first('name'));

        /*$v = new Validator(['name' => ''], ['name' => 'Required'], ['name.required' => ':attribute is required!', 'validation.attributes.name' => 'Name']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('Name is required!', $v->messages()->first('name'));*/

        //set customAttributes by setter
        $customAttributes = ['name' => 'Name'];
        $v = new Validator(['name' => ''], ['name' => 'Required'], ['name.required' => ':attribute is required!']);
        $v->addCustomAttributes($customAttributes);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('Name is required!', $v->messages()->first('name'));

        $v = new Validator(['name' => ''], ['name' => 'Required'], ['name.required' => ':attribute is required!']);
        $v->setAttributeNames(['name' => 'Name']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('Name is required!', $v->messages()->first('name'));

        $v = new Validator(['name' => ''], ['name' => 'Required'], ['name.required' => ':Attribute is required!']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('Name is required!', $v->messages()->first('name'));

        $v = new Validator(['name' => ''], ['name' => 'Required'], ['name.required' => ':ATTRIBUTE is required!']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('NAME is required!', $v->messages()->first('name'));
    }

    public function testDisplayableValuesAreReplaced()
    {
        //required_if:foo,bar
        
        $v = new Validator(['color' => '1', 'bar' => ''], ['bar' => 'RequiredIf:color,1'], ['name.required_if' => 'The :attribute field is required when :other is :value.', 'validation.values.color.1' => 'red']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('bar_RequiredIf', $v->messages()->first('bar'));

        //in:foo,bar,...
        $msg = [
            'validation.in' => ':attribute must be included in :values.',
            'validation.values.type.5' => 'Short',
            'validation.values.type.300' => 'Long'
        ];
        $v = new Validator(['type' => '4'], ['type' => 'in:5,300'], $msg);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('type_In', $v->messages()->first('type'));

        // test addCustomValues
        $customValues = [
            'type' => [
                '5'   => 'Short',
                '300' => 'Long',
            ],
        ];
        $msg = [
            'validation.in' => ':attribute must be included in :values.',
        ];
        $v = new Validator(['type' => '4'], ['type' => 'in:5,300'], $msg);
        $v->addCustomValues($customValues);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('type_In', $v->messages()->first('type'));

        // set custom values by setter
        $v = new Validator(['type' => '4'], ['type' => 'in:5,300'], [
            'validation.in' => ':attribute must be included in :values.',
            '5' => 'Short',
            '300' => 'Long',
        ]);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('type_In', $v->messages()->first('type'));
    }

    public function testCustomValidationLinesAreRespected()
    {
        $v = new Validator(['name' => ''], ['name' => 'Required'], ['required' => 'really required!']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('really required!', $v->messages()->first('name'));
    }

    public function testCustomValidationLinesAreRespectedWithAsterisks()
    {
        $v = new Validator(['name' => ['', '']], []);
        $v->each('name', 'required|max:255');
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('name.0_Required', $v->messages()->first('name.0'));
        $this->assertEquals('name.1_Required', $v->messages()->first('name.1'));
    }

    public function testInlineValidationMessagesAreRespected()
    {
        
        $v = new Validator(['name' => ''], ['name' => 'Required'], ['name.required' => 'require it please!']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('require it please!', $v->messages()->first('name'));

        
        $v = new Validator(['name' => ''], ['name' => 'Required'], ['required' => 'require it please!']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('require it please!', $v->messages()->first('name'));
    }

    public function testInlineValidationMessagesAreRespectedWithAsterisks()
    {
        
        $v = new Validator(['name' => ['', '']], [], ['name.*.required' => 'all must be required!']);
        $v->each('name', 'required|max:255');
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('all must be required!', $v->messages()->first('name.0'));
        $this->assertEquals('all must be required!', $v->messages()->first('name.1'));
    }

    public function testValidateArray()
    {
        $v = new Validator(['foo' => [1, 2, 3]], ['foo' => 'Array']);
        $this->assertTrue($v->passes());
    }

    public function testValidateFilled()
    {
        
        $v = new Validator([], ['name' => 'filled']);
        $this->assertTrue($v->passes());

        $v = new Validator(['name' => ''], ['name' => 'filled']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => [['id' => 1], []]], ['foo.*.id' => 'filled']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [['id' => '']]], ['foo.*.id' => 'filled']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => [['id' => null]]], ['foo.*.id' => 'filled']);
        $this->assertFalse($v->passes());
    }

    public function testValidatePresent()
    {
        
        $v = new Validator([], ['name' => 'present']);
        $this->assertFalse($v->passes());

        $v = new Validator(['name' => ''], ['name' => 'present']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [['id' => 1], ['name' => 'a']]], ['foo.*.id' => 'present']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => [['id' => 1], []]], ['foo.*.id' => 'present']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => [['id' => 1], ['id' => '']]], ['foo.*.id' => 'present']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [['id' => 1], ['id' => null]]], ['foo.*.id' => 'present']);
        $this->assertTrue($v->passes());
    }

    public function testValidateRequired()
    {
        $v = new Validator([], ['name' => 'Required']);
        $this->assertFalse($v->passes());

        $v = new Validator(['name' => ''], ['name' => 'Required']);
        $this->assertFalse($v->passes());

        $v = new Validator(['name' => 'foo'], ['name' => 'Required']);
        $this->assertTrue($v->passes());
    }

    public function testValidateRequiredWith()
    {
        
        $v = new Validator(['first' => 'Taylor'], ['last' => 'required_with:first']);
        $this->assertFalse($v->passes());

        $v = new Validator(['first' => 'Taylor', 'last' => ''], ['last' => 'required_with:first']);
        $this->assertFalse($v->passes());

        $v = new Validator(['first' => ''], ['last' => 'required_with:first']);
        $this->assertTrue($v->passes());

        $v = new Validator([], ['last' => 'required_with:first']);
        $this->assertTrue($v->passes());

        $v = new Validator(['first' => 'Taylor', 'last' => 'Otwell'], ['last' => 'required_with:first']);
        $this->assertTrue($v->passes());

    }

    public function testRequiredWithAll()
    {
        
        $v = new Validator(['first' => 'foo'], ['last' => 'required_with_all:first,foo']);
        $this->assertTrue($v->passes());

        $v = new Validator(['first' => 'foo'], ['last' => 'required_with_all:first']);
        $this->assertFalse($v->passes());
    }

    public function testValidateRequiredWithout()
    {
        
        $v = new Validator(['first' => 'Taylor'], ['last' => 'required_without:first']);
        $this->assertTrue($v->passes());

        $v = new Validator(['first' => 'Taylor', 'last' => ''], ['last' => 'required_without:first']);
        $this->assertTrue($v->passes());

        $v = new Validator(['first' => ''], ['last' => 'required_without:first']);
        $this->assertFalse($v->passes());

        $v = new Validator([], ['last' => 'required_without:first']);
        $this->assertFalse($v->passes());

        $v = new Validator(['first' => 'Taylor', 'last' => 'Otwell'], ['last' => 'required_without:first']);
        $this->assertTrue($v->passes());

        $v = new Validator(['last' => 'Otwell'], ['last' => 'required_without:first']);
        $this->assertTrue($v->passes());
    }

    public function testRequiredWithoutMultiple()
    {
        

        $rules = [
            'f1' => 'required_without:f2,f3',
            'f2' => 'required_without:f1,f3',
            'f3' => 'required_without:f1,f2',
        ];

        $v = new Validator([], $rules);
        $this->assertTrue($v->fails());

        $v = new Validator(['f1' => 'foo'], $rules);
        $this->assertTrue($v->fails());

        $v = new Validator(['f2' => 'foo'], $rules);
        $this->assertTrue($v->fails());

        $v = new Validator(['f3' => 'foo'], $rules);
        $this->assertTrue($v->fails());

        $v = new Validator(['f1' => 'foo', 'f2' => 'bar'], $rules);
        $this->assertTrue($v->passes());

        $v = new Validator(['f1' => 'foo', 'f3' => 'bar'], $rules);
        $this->assertTrue($v->passes());

        $v = new Validator(['f2' => 'foo', 'f3' => 'bar'], $rules);
        $this->assertTrue($v->passes());

        $v = new Validator(['f1' => 'foo', 'f2' => 'bar', 'f3' => 'baz'], $rules);
        $this->assertTrue($v->passes());
    }

    public function testRequiredWithoutAll()
    {


        $rules = [
            'f1' => 'required_without_all:f2,f3',
            'f2' => 'required_without_all:f1,f3',
            'f3' => 'required_without_all:f1,f2',
        ];

        $v = new Validator([], $rules);
        $this->assertTrue($v->fails());

        $v = new Validator(['f1' => 'foo'], $rules);
        $this->assertTrue($v->passes());

        $v = new Validator(['f2' => 'foo'], $rules);
        $this->assertTrue($v->passes());

        $v = new Validator(['f3' => 'foo'], $rules);
        $this->assertTrue($v->passes());

        $v = new Validator(['f1' => 'foo', 'f2' => 'bar'], $rules);
        $this->assertTrue($v->passes());

        $v = new Validator(['f1' => 'foo', 'f3' => 'bar'], $rules);
        $this->assertTrue($v->passes());

        $v = new Validator(['f2' => 'foo', 'f3' => 'bar'], $rules);
        $this->assertTrue($v->passes());

        $v = new Validator(['f1' => 'foo', 'f2' => 'bar', 'f3' => 'baz'], $rules);
        $this->assertTrue($v->passes());
    }

    public function testRequiredIf()
    {
        
        $v = new Validator(['first' => 'taylor'], ['last' => 'required_if:first,taylor']);
        $this->assertTrue($v->fails());

        
        $v = new Validator(['first' => 'taylor', 'last' => 'otwell'], ['last' => 'required_if:first,taylor']);
        $this->assertTrue($v->passes());

        
        $v = new Validator(['first' => 'taylor', 'last' => 'otwell'], ['last' => 'required_if:first,taylor,dayle']);
        $this->assertTrue($v->passes());

        
        $v = new Validator(['first' => 'dayle', 'last' => 'rees'], ['last' => 'required_if:first,taylor,dayle']);
        $this->assertTrue($v->passes());

        // error message when passed multiple values (required_if:foo,bar,baz)
        $v = new Validator(['first' => 'dayle', 'last' => ''], ['last' => 'RequiredIf:first,taylor,dayle'], ['name.required_if' => 'The :attribute field is required when :other is :value.']);
        $this->assertFalse($v->passes());
        $this->assertEquals('last_RequiredIf', $v->messages()->first('last'));
    }

    public function testRequiredUnless()
    {
        
        $v = new Validator(['first' => 'sven'], ['last' => 'required_unless:first,taylor']);
        $this->assertTrue($v->fails());

        
        $v = new Validator(['first' => 'taylor'], ['last' => 'required_unless:first,taylor']);
        $this->assertTrue($v->passes());

        
        $v = new Validator(['first' => 'sven', 'last' => 'wittevrongel'], ['last' => 'required_unless:first,taylor']);
        $this->assertTrue($v->passes());

        
        $v = new Validator(['first' => 'taylor'], ['last' => 'required_unless:first,taylor,sven']);
        $this->assertTrue($v->passes());

        
        $v = new Validator(['first' => 'sven'], ['last' => 'required_unless:first,taylor,sven']);
        $this->assertTrue($v->passes());

        // error message when passed multiple values (required_unless:foo,bar,baz)
        
        $v = new Validator(['first' => 'dayle', 'last' => ''], ['last' => 'RequiredUnless:first,taylor,sven']);
        $this->assertFalse($v->passes());
        $this->assertEquals('last_RequiredUnless', $v->messages()->first('last'));
    }

    public function testValidateInArray()
    {
        
        $v = new Validator(['foo' => [1, 2, 3], 'bar' => [1, 2]], ['foo.*' => 'in_array:bar.*']);
        $this->assertFalse($v->passes());

        
        $v = new Validator(['foo' => [1, 2], 'bar' => [1, 2, 3]], ['foo.*' => 'in_array:bar.*']);
        $this->assertTrue($v->passes());

        
        $v = new Validator(['foo' => [['bar_id' => 5], ['bar_id' => 2]], 'bar' => [['id' => 1, ['id' => 2]]]], ['foo.*.bar_id' => 'in_array:bar.*.id']);
        $this->assertFalse($v->passes());

        
        $v = new Validator(['foo' => [['bar_id' => 1], ['bar_id' => 2]], 'bar' => [['id' => 1, ['id' => 2]]]], ['foo.*.bar_id' => 'in_array:bar.*.id']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [1, 2, 3], 'bar' => [1, 2]], ['foo.*' => 'in_array:bar.*']);
        $this->assertEquals('foo.2_InArray', $v->messages()->first('foo.2'));
    }

    public function testValidateConfirmed()
    {
        
        $v = new Validator(['password' => 'foo'], ['password' => 'Confirmed']);
        $this->assertFalse($v->passes());

        $v = new Validator(['password' => 'foo', 'password_confirmation' => 'bar'], ['password' => 'Confirmed']);
        $this->assertFalse($v->passes());

        $v = new Validator(['password' => 'foo', 'password_confirmation' => 'foo'], ['password' => 'Confirmed']);
        $this->assertTrue($v->passes());

        $v = new Validator(['password' => '1e2', 'password_confirmation' => '100'], ['password' => 'Confirmed']);
        $this->assertFalse($v->passes());
    }

    public function testValidateSame()
    {
        
        $v = new Validator(['foo' => 'bar', 'baz' => 'boom'], ['foo' => 'Same:baz']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'bar'], ['foo' => 'Same:baz']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'bar', 'baz' => 'bar'], ['foo' => 'Same:baz']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '1e2', 'baz' => '100'], ['foo' => 'Same:baz']);
        $this->assertFalse($v->passes());
    }

    public function testValidateDifferent()
    {
        
        $v = new Validator(['foo' => 'bar', 'baz' => 'boom'], ['foo' => 'Different:baz']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 'bar'], ['foo' => 'Different:baz']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'bar', 'baz' => 'bar'], ['foo' => 'Different:baz']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '1e2', 'baz' => '100'], ['foo' => 'Different:baz']);
        $this->assertTrue($v->passes());
    }

    public function testValidateAccepted()
    {
        
        $v = new Validator(['foo' => 'no'], ['foo' => 'Accepted']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => null], ['foo' => 'Accepted']);
        $this->assertFalse($v->passes());

        $v = new Validator([], ['foo' => 'Accepted']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 0], ['foo' => 'Accepted']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => false], ['foo' => 'Accepted']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'false'], ['foo' => 'Accepted']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'yes'], ['foo' => 'Accepted']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 'on'], ['foo' => 'Accepted']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '1'], ['foo' => 'Accepted']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 1], ['foo' => 'Accepted']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => true], ['foo' => 'Accepted']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 'true'], ['foo' => 'Accepted']);
        $this->assertTrue($v->passes());
    }

    public function testValidateString()
    {
        
        $v = new Validator(['x' => 'aslsdlks'], ['x' => 'string']);
        $this->assertTrue($v->passes());

        
        $v = new Validator(['x' => ['blah' => 'test']], ['x' => 'string']);
        $this->assertFalse($v->passes());
    }

    public function testValidateJson()
    {
        
        $v = new Validator(['foo' => 'aslksd'], ['foo' => 'json']);
        $this->assertFalse($v->passes());

        
        $v = new Validator(['foo' => '[]'], ['foo' => 'json']);
        $this->assertTrue($v->passes());

        
        $v = new Validator(['foo' => '{"name":"John","age":"34"}'], ['foo' => 'json']);
        $this->assertTrue($v->passes());
    }

    public function testValidateBoolean()
    {
        
        $v = new Validator(['foo' => 'no'], ['foo' => 'Boolean']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'yes'], ['foo' => 'Boolean']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'false'], ['foo' => 'Boolean']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'true'], ['foo' => 'Boolean']);
        $this->assertFalse($v->passes());

        $v = new Validator([], ['foo' => 'Boolean']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => false], ['foo' => 'Boolean']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => true], ['foo' => 'Boolean']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '1'], ['foo' => 'Boolean']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 1], ['foo' => 'Boolean']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '0'], ['foo' => 'Boolean']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 0], ['foo' => 'Boolean']);
        $this->assertTrue($v->passes());
    }

    public function testValidateBool()
    {
        
        $v = new Validator(['foo' => 'no'], ['foo' => 'Bool']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'yes'], ['foo' => 'Bool']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'false'], ['foo' => 'Bool']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'true'], ['foo' => 'Bool']);
        $this->assertFalse($v->passes());

        $v = new Validator([], ['foo' => 'Bool']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => false], ['foo' => 'Bool']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => true], ['foo' => 'Bool']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '1'], ['foo' => 'Bool']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 1], ['foo' => 'Bool']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '0'], ['foo' => 'Bool']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 0], ['foo' => 'Bool']);
        $this->assertTrue($v->passes());
    }

    public function testValidateNumeric()
    {
        
        $v = new Validator(['foo' => 'asdad'], ['foo' => 'Numeric']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '1.23'], ['foo' => 'Numeric']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '-1'], ['foo' => 'Numeric']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '1'], ['foo' => 'Numeric']);
        $this->assertTrue($v->passes());
    }

    public function testValidateInteger()
    {
        
        $v = new Validator(['foo' => 'asdad'], ['foo' => 'Integer']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '1.23'], ['foo' => 'Integer']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '-1'], ['foo' => 'Integer']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '1'], ['foo' => 'Integer']);
        $this->assertTrue($v->passes());
    }

    public function testValidateInt()
    {
        
        $v = new Validator(['foo' => 'asdad'], ['foo' => 'Int']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '1.23'], ['foo' => 'Int']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '-1'], ['foo' => 'Int']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '1'], ['foo' => 'Int']);
        $this->assertTrue($v->passes());
    }

    public function testValidateDigits()
    {
        
        $v = new Validator(['foo' => '12345'], ['foo' => 'Digits:5']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '123'], ['foo' => 'Digits:200']);
        $this->assertFalse($v->passes());

        
        $v = new Validator(['foo' => '12345'], ['foo' => 'digits_between:1,6']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 'bar'], ['foo' => 'digits_between:1,10']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '123'], ['foo' => 'digits_between:4,5']);
        $this->assertFalse($v->passes());
    }

    public function testValidateSize()
    {
        $v = new Validator(['foo' => 'asdad'], ['foo' => 'Size:3']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'anc'], ['foo' => 'Size:3']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '123'], ['foo' => 'Numeric|Size:3']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '3'], ['foo' => 'Numeric|Size:3']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [1, 2, 3]], ['foo' => 'Array|Size:3']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [1, 2, 3]], ['foo' => 'Array|Size:4']);
        $this->assertFalse($v->passes());
    }

    public function testValidateBetween()
    {
        
        $v = new Validator(['foo' => 'asdad'], ['foo' => 'Between:3,4']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'anc'], ['foo' => 'Between:3,5']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 'ancf'], ['foo' => 'Between:3,5']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 'ancfs'], ['foo' => 'Between:3,5']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '123'], ['foo' => 'Numeric|Between:50,100']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '3'], ['foo' => 'Numeric|Between:1,5']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [1, 2, 3]], ['foo' => 'Array|Between:1,5']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [1, 2, 3]], ['foo' => 'Array|Between:1,2']);
        $this->assertFalse($v->passes());
    }

    public function testValidateMin()
    {
        
        $v = new Validator(['foo' => '3'], ['foo' => 'Min:3']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'anc'], ['foo' => 'Min:3']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '2'], ['foo' => 'Numeric|Min:3']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '5'], ['foo' => 'Numeric|Min:3']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [1, 2, 3, 4]], ['foo' => 'Array|Min:3']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [1, 2]], ['foo' => 'Array|Min:3']);
        $this->assertFalse($v->passes());
    }

    public function testValidateMax()
    {
        
        $v = new Validator(['foo' => 'aslksd'], ['foo' => 'Max:3']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'anc'], ['foo' => 'Max:3']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => '211'], ['foo' => 'Numeric|Max:100']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => '22'], ['foo' => 'Numeric|Max:33']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [1, 2, 3]], ['foo' => 'Array|Max:4']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [1, 2, 3]], ['foo' => 'Array|Max:2']);
        $this->assertFalse($v->passes());
    }

    public function testProperMessagesAreReturnedForSizes()
    {
        $v = new Validator(['name' => '3'], ['name' => 'Numeric|Min:5']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('name_Min', $v->messages()->first('name'));

        $v = new Validator(['name' => 'asasdfadsfd'], ['name' => 'Size:2']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('name_Size', $v->messages()->first('name'));
    }

    public function testValidateIn()
    {
        $v = new Validator(['name' => 'foo'], ['name' => 'In:bar,baz']);
        $this->assertFalse($v->passes());

        
        $v = new Validator(['name' => 0], ['name' => 'In:bar,baz']);
        $this->assertFalse($v->passes());

        $v = new Validator(['name' => 'foo'], ['name' => 'In:foo,baz']);
        $this->assertTrue($v->passes());

        $v = new Validator(['name' => ['foo', 'bar']], ['name' => 'Array|In:foo,baz']);
        $this->assertFalse($v->passes());

        $v = new Validator(['name' => ['foo', 'qux']], ['name' => 'Array|In:foo,baz,qux']);
        $this->assertTrue($v->passes());

        $v = new Validator(['name' => ['foo', 'bar']], ['name' => 'Alpha|In:foo,bar']);
        $this->assertFalse($v->passes());
    }

    public function testValidateNotIn()
    {
        
        $v = new Validator(['name' => 'foo'], ['name' => 'NotIn:bar,baz']);
        $this->assertTrue($v->passes());

        $v = new Validator(['name' => 'foo'], ['name' => 'NotIn:foo,baz']);
        $this->assertFalse($v->passes());
    }

    public function testValidateDistinct()
    {
        $v = new Validator(['foo' => ['foo', 'foo']], ['foo.*' => 'distinct']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => ['foo', 'bar']], ['foo.*' => 'distinct']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => ['bar' => ['id' => 1], 'baz' => ['id' => 1]]], ['foo.*.id' => 'distinct']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => ['bar' => ['id' => 1], 'baz' => ['id' => 2]]], ['foo.*.id' => 'distinct']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [['id' => 1], ['id' => 1]]], ['foo.*.id' => 'distinct']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => [['id' => 1], ['id' => 2]]], ['foo.*.id' => 'distinct']);
        $this->assertTrue($v->passes());

        $v = new Validator(['cat' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 1]]]]], ['cat.*.prod.*.id' => 'distinct']);
        $this->assertFalse($v->passes());

        $v = new Validator(['cat' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 2]]]]], ['cat.*.prod.*.id' => 'distinct']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => ['foo', 'foo']], ['foo.*' => 'distinct'], ['foo.*.distinct' => 'There is a duplication!']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('There is a duplication!', $v->messages()->first('foo.0'));
        $this->assertEquals('There is a duplication!', $v->messages()->first('foo.1'));
    }

    public function testValidateIp()
    {
        $v = new Validator(['ip' => 'aslsdlks'], ['ip' => 'Ip']);
        $this->assertFalse($v->passes());

        $v = new Validator(['ip' => '127.0.0.1'], ['ip' => 'Ip']);
        $this->assertTrue($v->passes());
    }

    public function testValidateEmail()
    {
        $v = new Validator(['x' => 'aslsdlks'], ['x' => 'Email']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => 'foo@gmail.com'], ['x' => 'Email']);
        $this->assertTrue($v->passes());
    }

    /**
     * @dataProvider validUrls
     */
    public function testValidateUrlWithValidUrls($validUrl)
    {
        
        $v = new Validator(['x' => $validUrl], ['x' => 'Url']);
        $this->assertTrue($v->passes());
    }

    /**
     * @dataProvider invalidUrls
     */
    public function testValidateUrlWithInvalidUrls($invalidUrl)
    {
        $v = new Validator(['x' => $invalidUrl], ['x' => 'Url']);
        $this->assertFalse($v->passes());
    }

    public function validUrls()
    {
        return [
            ['aaa://fully.qualified.domain/path'],
            ['aaas://fully.qualified.domain/path'],
            ['about://fully.qualified.domain/path'],
            ['acap://fully.qualified.domain/path'],
            ['acct://fully.qualified.domain/path'],
            ['acr://fully.qualified.domain/path'],
            ['adiumxtra://fully.qualified.domain/path'],
            ['afp://fully.qualified.domain/path'],
            ['afs://fully.qualified.domain/path'],
            ['aim://fully.qualified.domain/path'],
            ['apt://fully.qualified.domain/path'],
            ['attachment://fully.qualified.domain/path'],
            ['aw://fully.qualified.domain/path'],
            ['barion://fully.qualified.domain/path'],
            ['beshare://fully.qualified.domain/path'],
            ['bitcoin://fully.qualified.domain/path'],
            ['blob://fully.qualified.domain/path'],
            ['bolo://fully.qualified.domain/path'],
            ['callto://fully.qualified.domain/path'],
            ['cap://fully.qualified.domain/path'],
            ['chrome://fully.qualified.domain/path'],
            ['chrome-extension://fully.qualified.domain/path'],
            ['cid://fully.qualified.domain/path'],
            ['coap://fully.qualified.domain/path'],
            ['coaps://fully.qualified.domain/path'],
            ['com-eventbrite-attendee://fully.qualified.domain/path'],
            ['content://fully.qualified.domain/path'],
            ['crid://fully.qualified.domain/path'],
            ['cvs://fully.qualified.domain/path'],
            ['data://fully.qualified.domain/path'],
            ['dav://fully.qualified.domain/path'],
            ['dict://fully.qualified.domain/path'],
            ['dlna-playcontainer://fully.qualified.domain/path'],
            ['dlna-playsingle://fully.qualified.domain/path'],
            ['dns://fully.qualified.domain/path'],
            ['dntp://fully.qualified.domain/path'],
            ['dtn://fully.qualified.domain/path'],
            ['dvb://fully.qualified.domain/path'],
            ['ed2k://fully.qualified.domain/path'],
            ['example://fully.qualified.domain/path'],
            ['facetime://fully.qualified.domain/path'],
            ['fax://fully.qualified.domain/path'],
            ['feed://fully.qualified.domain/path'],
            ['feedready://fully.qualified.domain/path'],
            ['file://fully.qualified.domain/path'],
            ['filesystem://fully.qualified.domain/path'],
            ['finger://fully.qualified.domain/path'],
            ['fish://fully.qualified.domain/path'],
            ['ftp://fully.qualified.domain/path'],
            ['geo://fully.qualified.domain/path'],
            ['gg://fully.qualified.domain/path'],
            ['git://fully.qualified.domain/path'],
            ['gizmoproject://fully.qualified.domain/path'],
            ['go://fully.qualified.domain/path'],
            ['gopher://fully.qualified.domain/path'],
            ['gtalk://fully.qualified.domain/path'],
            ['h323://fully.qualified.domain/path'],
            ['ham://fully.qualified.domain/path'],
            ['hcp://fully.qualified.domain/path'],
            ['http://fully.qualified.domain/path'],
            ['https://fully.qualified.domain/path'],
            ['iax://fully.qualified.domain/path'],
            ['icap://fully.qualified.domain/path'],
            ['icon://fully.qualified.domain/path'],
            ['im://fully.qualified.domain/path'],
            ['imap://fully.qualified.domain/path'],
            ['info://fully.qualified.domain/path'],
            ['iotdisco://fully.qualified.domain/path'],
            ['ipn://fully.qualified.domain/path'],
            ['ipp://fully.qualified.domain/path'],
            ['ipps://fully.qualified.domain/path'],
            ['irc://fully.qualified.domain/path'],
            ['irc6://fully.qualified.domain/path'],
            ['ircs://fully.qualified.domain/path'],
            ['iris://fully.qualified.domain/path'],
            ['iris.beep://fully.qualified.domain/path'],
            ['iris.lwz://fully.qualified.domain/path'],
            ['iris.xpc://fully.qualified.domain/path'],
            ['iris.xpcs://fully.qualified.domain/path'],
            ['itms://fully.qualified.domain/path'],
            ['jabber://fully.qualified.domain/path'],
            ['jar://fully.qualified.domain/path'],
            ['jms://fully.qualified.domain/path'],
            ['keyparc://fully.qualified.domain/path'],
            ['lastfm://fully.qualified.domain/path'],
            ['ldap://fully.qualified.domain/path'],
            ['ldaps://fully.qualified.domain/path'],
            ['magnet://fully.qualified.domain/path'],
            ['mailserver://fully.qualified.domain/path'],
            ['mailto://fully.qualified.domain/path'],
            ['maps://fully.qualified.domain/path'],
            ['market://fully.qualified.domain/path'],
            ['message://fully.qualified.domain/path'],
            ['mid://fully.qualified.domain/path'],
            ['mms://fully.qualified.domain/path'],
            ['modem://fully.qualified.domain/path'],
            ['ms-help://fully.qualified.domain/path'],
            ['ms-settings://fully.qualified.domain/path'],
            ['ms-settings-airplanemode://fully.qualified.domain/path'],
            ['ms-settings-bluetooth://fully.qualified.domain/path'],
            ['ms-settings-camera://fully.qualified.domain/path'],
            ['ms-settings-cellular://fully.qualified.domain/path'],
            ['ms-settings-cloudstorage://fully.qualified.domain/path'],
            ['ms-settings-emailandaccounts://fully.qualified.domain/path'],
            ['ms-settings-language://fully.qualified.domain/path'],
            ['ms-settings-location://fully.qualified.domain/path'],
            ['ms-settings-lock://fully.qualified.domain/path'],
            ['ms-settings-nfctransactions://fully.qualified.domain/path'],
            ['ms-settings-notifications://fully.qualified.domain/path'],
            ['ms-settings-power://fully.qualified.domain/path'],
            ['ms-settings-privacy://fully.qualified.domain/path'],
            ['ms-settings-proximity://fully.qualified.domain/path'],
            ['ms-settings-screenrotation://fully.qualified.domain/path'],
            ['ms-settings-wifi://fully.qualified.domain/path'],
            ['ms-settings-workplace://fully.qualified.domain/path'],
            ['msnim://fully.qualified.domain/path'],
            ['msrp://fully.qualified.domain/path'],
            ['msrps://fully.qualified.domain/path'],
            ['mtqp://fully.qualified.domain/path'],
            ['mumble://fully.qualified.domain/path'],
            ['mupdate://fully.qualified.domain/path'],
            ['mvn://fully.qualified.domain/path'],
            ['news://fully.qualified.domain/path'],
            ['nfs://fully.qualified.domain/path'],
            ['ni://fully.qualified.domain/path'],
            ['nih://fully.qualified.domain/path'],
            ['nntp://fully.qualified.domain/path'],
            ['notes://fully.qualified.domain/path'],
            ['oid://fully.qualified.domain/path'],
            ['opaquelocktoken://fully.qualified.domain/path'],
            ['pack://fully.qualified.domain/path'],
            ['palm://fully.qualified.domain/path'],
            ['paparazzi://fully.qualified.domain/path'],
            ['pkcs11://fully.qualified.domain/path'],
            ['platform://fully.qualified.domain/path'],
            ['pop://fully.qualified.domain/path'],
            ['pres://fully.qualified.domain/path'],
            ['prospero://fully.qualified.domain/path'],
            ['proxy://fully.qualified.domain/path'],
            ['psyc://fully.qualified.domain/path'],
            ['query://fully.qualified.domain/path'],
            ['redis://fully.qualified.domain/path'],
            ['rediss://fully.qualified.domain/path'],
            ['reload://fully.qualified.domain/path'],
            ['res://fully.qualified.domain/path'],
            ['resource://fully.qualified.domain/path'],
            ['rmi://fully.qualified.domain/path'],
            ['rsync://fully.qualified.domain/path'],
            ['rtmfp://fully.qualified.domain/path'],
            ['rtmp://fully.qualified.domain/path'],
            ['rtsp://fully.qualified.domain/path'],
            ['rtsps://fully.qualified.domain/path'],
            ['rtspu://fully.qualified.domain/path'],
            ['secondlife://fully.qualified.domain/path'],
            ['service://fully.qualified.domain/path'],
            ['session://fully.qualified.domain/path'],
            ['sftp://fully.qualified.domain/path'],
            ['sgn://fully.qualified.domain/path'],
            ['shttp://fully.qualified.domain/path'],
            ['sieve://fully.qualified.domain/path'],
            ['sip://fully.qualified.domain/path'],
            ['sips://fully.qualified.domain/path'],
            ['skype://fully.qualified.domain/path'],
            ['smb://fully.qualified.domain/path'],
            ['sms://fully.qualified.domain/path'],
            ['smtp://fully.qualified.domain/path'],
            ['snews://fully.qualified.domain/path'],
            ['snmp://fully.qualified.domain/path'],
            ['soap.beep://fully.qualified.domain/path'],
            ['soap.beeps://fully.qualified.domain/path'],
            ['soldat://fully.qualified.domain/path'],
            ['spotify://fully.qualified.domain/path'],
            ['ssh://fully.qualified.domain/path'],
            ['steam://fully.qualified.domain/path'],
            ['stun://fully.qualified.domain/path'],
            ['stuns://fully.qualified.domain/path'],
            ['submit://fully.qualified.domain/path'],
            ['svn://fully.qualified.domain/path'],
            ['tag://fully.qualified.domain/path'],
            ['teamspeak://fully.qualified.domain/path'],
            ['tel://fully.qualified.domain/path'],
            ['teliaeid://fully.qualified.domain/path'],
            ['telnet://fully.qualified.domain/path'],
            ['tftp://fully.qualified.domain/path'],
            ['things://fully.qualified.domain/path'],
            ['thismessage://fully.qualified.domain/path'],
            ['tip://fully.qualified.domain/path'],
            ['tn3270://fully.qualified.domain/path'],
            ['turn://fully.qualified.domain/path'],
            ['turns://fully.qualified.domain/path'],
            ['tv://fully.qualified.domain/path'],
            ['udp://fully.qualified.domain/path'],
            ['unreal://fully.qualified.domain/path'],
            ['urn://fully.qualified.domain/path'],
            ['ut2004://fully.qualified.domain/path'],
            ['vemmi://fully.qualified.domain/path'],
            ['ventrilo://fully.qualified.domain/path'],
            ['videotex://fully.qualified.domain/path'],
            ['view-source://fully.qualified.domain/path'],
            ['wais://fully.qualified.domain/path'],
            ['webcal://fully.qualified.domain/path'],
            ['ws://fully.qualified.domain/path'],
            ['wss://fully.qualified.domain/path'],
            ['wtai://fully.qualified.domain/path'],
            ['wyciwyg://fully.qualified.domain/path'],
            ['xcon://fully.qualified.domain/path'],
            ['xcon-userid://fully.qualified.domain/path'],
            ['xfire://fully.qualified.domain/path'],
            ['xmlrpc.beep://fully.qualified.domain/path'],
            ['xmlrpc.beeps://fully.qualified.domain/path'],
            ['xmpp://fully.qualified.domain/path'],
            ['xri://fully.qualified.domain/path'],
            ['ymsgr://fully.qualified.domain/path'],
            ['z39.50://fully.qualified.domain/path'],
            ['z39.50r://fully.qualified.domain/path'],
            ['z39.50s://fully.qualified.domain/path'],
            ['http://a.pl'],
            ['http://localhost/url.php'],
            ['http://local.dev'],
            ['http://google.com'],
            ['http://www.google.com'],
            ['https://google.com'],
            ['http://illuminate.dev'],
            ['http://localhost'],
            ['http://laravel.com/?'],
            ['http://президент.рф/'],
            ['http://스타벅스코리아.com'],
            ['http://xn--d1abbgf6aiiy.xn--p1ai/'],
        ];
    }

    public function invalidUrls()
    {
        return [

            ['aslsdlks'],
            ['google.com'],
            ['://google.com'],
            ['http ://google.com'],
            ['http:/google.com'],
            ['http://goog_le.com'],
            ['http://google.com::aa'],
            ['http://google.com:aa'],
            ['http://laravel.com?'],
            ['http://laravel.com#'],
            ['http://127.0.0.1:aa'],
            ['http://[::1'],
            ['foo://bar'],
            ['javascript://test%0Aalert(321)'],
        ];
    }

    public function testValidateActiveUrl()
    {
        $v = new Validator(['x' => 'aslsdlks'], ['x' => 'active_url']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => 'http://google.com'], ['x' => 'active_url']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'http://www.google.com'], ['x' => 'active_url']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'http://www.google.com/about'], ['x' => 'active_url']);
        $this->assertTrue($v->passes());
    }

    public function testEmptyRulesSkipped()
    {
        
        $v = new Validator(['x' => 'aslsdlks'], ['x' => ['alpha', [], '']]);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'aslsdlks'], ['x' => '|||required|']);
        $this->assertTrue($v->passes());
    }

    public function testAlternativeFormat()
    {
        
        $v = new Validator(['x' => 'aslsdlks'], ['x' => ['alpha', ['min', 3], ['max', 10]]]);
        $this->assertTrue($v->passes());
    }

    public function testValidateAlpha()
    {
        
        $v = new Validator(['x' => 'aslsdlks'], ['x' => 'Alpha']);
        $this->assertTrue($v->passes());

        
        $v = new Validator(['x' => 'aslsdlks
1
1'], ['x' => 'Alpha']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => 'http://google.com'], ['x' => 'Alpha']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => 'ユニコードを基盤技術と'], ['x' => 'Alpha']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'ユニコード を基盤技術と'], ['x' => 'Alpha']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => 'नमस्कार'], ['x' => 'Alpha']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'आपका स्वागत है'], ['x' => 'Alpha']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => 'Continuación'], ['x' => 'Alpha']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'ofreció su dimisión'], ['x' => 'Alpha']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => '❤'], ['x' => 'Alpha']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => '123'], ['x' => 'Alpha']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => 123], ['x' => 'Alpha']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => 'abc123'], ['x' => 'Alpha']);
        $this->assertFalse($v->passes());
    }

    public function testValidateAlphaNum()
    {
        
        $v = new Validator(['x' => 'asls13dlks'], ['x' => 'AlphaNum']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'http://g232oogle.com'], ['x' => 'AlphaNum']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => '१२३'], ['x' => 'AlphaNum']); // numbers in Hindi
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => '٧٨٩'], ['x' => 'AlphaNum']); // eastern arabic numerals
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'नमस्कार'], ['x' => 'AlphaNum']);
        $this->assertTrue($v->passes());
    }

    public function testValidateAlphaDash()
    {
        
        $v = new Validator(['x' => 'asls1-_3dlks'], ['x' => 'AlphaDash']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'http://-g232oogle.com'], ['x' => 'AlphaDash']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => 'नमस्कार-_'], ['x' => 'AlphaDash']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => '٧٨٩'], ['x' => 'AlphaDash']); // eastern arabic numerals
        $this->assertTrue($v->passes());
    }

    public function testValidateTimezone()
    {
        
        $v = new Validator(['foo' => 'India'], ['foo' => 'Timezone']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'Cairo'], ['foo' => 'Timezone']);
        $this->assertFalse($v->passes());

        $v = new Validator(['foo' => 'UTC'], ['foo' => 'Timezone']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 'Africa/Windhoek'], ['foo' => 'Timezone']);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => 'GMT'], ['foo' => 'Timezone']);
        $this->assertTrue($v->passes());
    }

    public function testValidateRegex()
    {
        
        $v = new Validator(['x' => 'asdasdf'], ['x' => 'Regex:/^([a-z])+$/i']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'aasd234fsd1'], ['x' => 'Regex:/^([a-z])+$/i']);
        $this->assertFalse($v->passes());

        $v = new Validator(['x' => 'a,b'], ['x' => 'Regex:/^a,b$/i']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => '12'], ['x' => 'Regex:/^12$/i']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 123], ['x' => 'Regex:/^123$/i']);
        $this->assertTrue($v->passes());
    }

    public function testValidateDateAndFormat()
    {
        date_default_timezone_set('UTC');
        
        $v = new Validator(['x' => '2000-01-01'], ['x' => 'date']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => '01/01/2000'], ['x' => 'date']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => 'Not a date'], ['x' => 'date']);
        $this->assertTrue($v->fails());

        $v = new Validator(['x' => '2000-01-01'], ['x' => 'date_format:Y-m-d']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => '2000-01-01 17:43:59'], ['x' => 'date_format:Y-m-d H:i:s']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => '01/01/2001'], ['x' => 'date_format:Y-m-d']);
        $this->assertTrue($v->fails());

        $v = new Validator(['x' => '22000-01-01'], ['x' => 'date_format:Y-m-d']);
        $this->assertTrue($v->fails());
    }

    public function testBeforeAndAfter()
    {
        date_default_timezone_set('UTC');
        
        $v = new Validator(['x' => '2000-01-01'], ['x' => 'Before:2012-01-01']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => '2012-01-01'], ['x' => 'After:2000-01-01']);
        $this->assertTrue($v->passes());

        $v = new Validator(['start' => '2012-01-01', 'ends' => '2013-01-01'], ['start' => 'After:2000-01-01', 'ends' => 'After:start']);
        $this->assertTrue($v->passes());

        $v = new Validator(['start' => '2012-01-01', 'ends' => '2000-01-01'], ['start' => 'After:2000-01-01', 'ends' => 'After:start']);
        $this->assertTrue($v->fails());

        $v = new Validator(['start' => '2012-01-01', 'ends' => '2013-01-01'], ['start' => 'Before:ends', 'ends' => 'After:start']);
        $this->assertTrue($v->passes());

        $v = new Validator(['start' => '2012-01-01', 'ends' => '2000-01-01'], ['start' => 'Before:ends', 'ends' => 'After:start']);
        $this->assertTrue($v->fails());
    }

    public function testBeforeAndAfterWithFormat()
    {
        date_default_timezone_set('UTC');
        
        $v = new Validator(['x' => '31/12/2000'], ['x' => 'before:31/02/2012']);
        $this->assertTrue($v->fails());

        $v = new Validator(['x' => '31/12/2000'], ['x' => 'date_format:d/m/Y|before:31/12/2012']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => '31/12/2012'], ['x' => 'after:31/12/2000']);
        $this->assertTrue($v->fails());

        $v = new Validator(['x' => '31/12/2012'], ['x' => 'date_format:d/m/Y|after:31/12/2000']);
        $this->assertTrue($v->passes());

        $v = new Validator(['start' => '31/12/2012', 'ends' => '31/12/2013'], ['start' => 'after:01/01/2000', 'ends' => 'after:start']);
        $this->assertTrue($v->fails());

        $v = new Validator(['start' => '31/12/2012', 'ends' => '31/12/2013'], ['start' => 'date_format:d/m/Y|after:31/12/2000', 'ends' => 'date_format:d/m/Y|after:start']);
        $this->assertTrue($v->passes());

        $v = new Validator(['start' => '31/12/2012', 'ends' => '31/12/2000'], ['start' => 'after:31/12/2000', 'ends' => 'after:start']);
        $this->assertTrue($v->fails());

        $v = new Validator(['start' => '31/12/2012', 'ends' => '31/12/2000'], ['start' => 'date_format:d/m/Y|after:31/12/2000', 'ends' => 'date_format:d/m/Y|after:start']);
        $this->assertTrue($v->fails());

        $v = new Validator(['start' => '31/12/2012', 'ends' => '31/12/2013'], ['start' => 'before:ends', 'ends' => 'after:start']);
        $this->assertTrue($v->fails());

        $v = new Validator(['start' => '31/12/2012', 'ends' => '31/12/2013'], ['start' => 'date_format:d/m/Y|before:ends', 'ends' => 'date_format:d/m/Y|after:start']);
        $this->assertTrue($v->passes());

        $v = new Validator(['start' => '31/12/2012', 'ends' => '31/12/2000'], ['start' => 'before:ends', 'ends' => 'after:start']);
        $this->assertTrue($v->fails());

        $v = new Validator(['start' => '31/12/2012', 'ends' => '31/12/2000'], ['start' => 'date_format:d/m/Y|before:ends', 'ends' => 'date_format:d/m/Y|after:start']);
        $this->assertTrue($v->fails());

        $v = new Validator(['start' => 'invalid', 'ends' => 'invalid'], ['start' => 'date_format:d/m/Y|before:ends', 'ends' => 'date_format:d/m/Y|after:start']);
        $this->assertTrue($v->fails());

        $v = new Validator(['x' => date('d/m/Y')], ['x' => 'date_format:d/m/Y|after:yesterday|before:tomorrow']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => date('d/m/Y')], ['x' => 'date_format:d/m/Y|after:tomorrow|before:yesterday']);
        $this->assertTrue($v->fails());

        $v = new Validator(['x' => date('Y-m-d')], ['x' => 'after:yesterday|before:tomorrow']);
        $this->assertTrue($v->passes());

        $v = new Validator(['x' => date('Y-m-d')], ['x' => 'after:tomorrow|before:yesterday']);
        $this->assertTrue($v->fails());
    }

    public function testSometimesAddingRules()
    {
        
        $v = new Validator(['x' => 'foo'], ['x' => 'Required']);
        $v->sometimes('x', 'Confirmed', function ($i) { return $i->x == 'foo'; });
        $this->assertEquals(['x' => ['Required', 'Confirmed']], $v->getRules());

        
        $v = new Validator(['x' => 'foo'], ['x' => 'Required']);
        $v->sometimes('x', 'Confirmed', function ($i) { return $i->x == 'bar'; });
        $this->assertEquals(['x' => ['Required']], $v->getRules());

        
        $v = new Validator(['x' => 'foo'], ['x' => 'Required']);
        $v->sometimes('x', 'Foo|Bar', function ($i) { return $i->x == 'foo'; });
        $this->assertEquals(['x' => ['Required', 'Foo', 'Bar']], $v->getRules());

        
        $v = new Validator(['x' => 'foo'], ['x' => 'Required']);
        $v->sometimes('x', ['Foo', 'Bar:Baz'], function ($i) { return $i->x == 'foo'; });
        $this->assertEquals(['x' => ['Required', 'Foo', 'Bar:Baz']], $v->getRules());
    }

    public function testCustomValidators()
    {
        $v = new Validator(['name' => 'taylor'], ['name' => 'foo']);
        $v->addExtension('foo', function () { return false; });
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('name_Foo', $v->messages()->first('name'));

        $v = new Validator(['name' => 'taylor'], ['name' => 'foo_bar']);
        $v->addExtension('FooBar', function () { return false; });
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('name_FooBar', $v->messages()->first('name'));

        $v = new Validator(['name' => 'taylor'], ['name' => 'foo_bar']);
        $v->addExtension('FooBar', function () { return false; });
        $v->setFallbackMessages(['foo_bar' => 'foo!']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('name_FooBar', $v->messages()->first('name'));

        $v = new Validator(['name' => 'taylor'], ['name' => 'foo_bar']);
        $v->addExtensions(['FooBar' => function () { return false; }]);
        $v->setFallbackMessages(['foo_bar' => 'foo!']);
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('name_FooBar', $v->messages()->first('name'));
    }

    public function testClassBasedCustomValidators()
    {
        $v = new Validator(['name' => 'taylor'], ['name' => 'foo']);
        $v->addExtension('foo', 'Foo@bar');
        $this->assertFalse($v->passes());
        $v->messages()->setFormat(':message');
        $this->assertEquals('name_Foo', $v->messages()->first('name'));
    }

    public function testCustomImplicitValidators()
    {
        $v = new Validator([], ['implicit_rule' => 'foo']);
        $v->addImplicitExtension('implicit_rule', function () { return true; });
        $this->assertTrue($v->passes());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionThrownOnIncorrectParameterCount()
    {
        $v = new Validator([], ['foo' => 'required_if:foo']);
        $v->passes();
    }

    public function testValidateEach()
    {
        $data = ['foo' => [5, 10, 15]];

        $v = new Validator($data, ['foo' => 'Array']);
        $v->each('foo', ['numeric|min:6|max:14']);
        $this->assertFalse($v->passes());

        $v = new Validator($data, ['foo' => 'Array']);
        $v->each('foo', ['numeric|min:4|max:16']);
        $this->assertTrue($v->passes());

        $v = new Validator($data, ['foo' => 'Array']);
        $v->each('foo', 'numeric|min:4|max:16');
        $this->assertTrue($v->passes());

        $v = new Validator($data, ['foo' => 'Array']);
        $v->each('foo', ['numeric', 'min:6', 'max:14']);
        $this->assertFalse($v->passes());

        $v = new Validator($data, ['foo' => 'Array']);
        $v->each('foo', ['numeric', 'min:4', 'max:16']);
        $this->assertTrue($v->passes());
    }

    public function testValidateImplicitEachWithAsterisks()
    {
        
        $data = ['foo' => [5, 10, 15]];

        // pipe rules fails
        $v = new Validator($data, [
            'foo' => 'Array',
            'foo.*' => 'Numeric|Min:6|Max:16',
        ]);
        $this->assertFalse($v->passes());

        // pipe passes
        $v = new Validator($data, [
            'foo' => 'Array',
            'foo.*' => 'Numeric|Min:4|Max:16',
        ]);
        $this->assertTrue($v->passes());

        // array rules fails
        $v = new Validator($data, [
            'foo' => 'Array',
            'foo.*' => ['Numeric', 'Min:6', 'Max:16'],
        ]);
        $this->assertFalse($v->passes());

        // array rules passes
        $v = new Validator($data, [
            'foo' => 'Array',
            'foo.*' => ['Numeric', 'Min:4', 'Max:16'],
        ]);
        $this->assertTrue($v->passes());

        // string passes
        $v = new Validator(
            ['foo' => [['name' => 'first'], ['name' => 'second']]],
            ['foo' => 'Array', 'foo.*.name' => 'Required|String']);
        $this->assertTrue($v->passes());

        // numeric fails
        $v = new Validator(
            ['foo' => [['name' => 'first'], ['name' => 'second']]],
            ['foo' => 'Array', 'foo.*.name' => 'Required|Numeric']);
        $this->assertFalse($v->passes());

        // nested array fails
        $v = new Validator(
            ['foo' => [['name' => 'first', 'votes' => [1, 2]], ['name' => 'second', 'votes' => ['something', 2]]]],
            ['foo' => 'Array', 'foo.*.name' => 'Required|String', 'foo.*.votes.*' => 'Required|Integer']);
        $this->assertFalse($v->passes());

        // multiple items passes
        $v = new Validator(['foo' => [['name' => 'first'], ['name' => 'second']]],
            ['foo' => 'Array', 'foo.*.name' => ['Required', 'String']]);
        $this->assertTrue($v->passes());

        // multiple items fails
        $v = new Validator(['foo' => [['name' => 'first'], ['name' => 'second']]],
            ['foo' => 'Array', 'foo.*.name' => ['Required', 'Numeric']]);
        $this->assertFalse($v->passes());

        // nested arrays fails
        $v = new Validator(
            ['foo' => [['name' => 'first', 'votes' => [1, 2]], ['name' => 'second', 'votes' => ['something', 2]]]],
            ['foo' => 'Array', 'foo.*.name' => ['Required', 'String'], 'foo.*.votes.*' => ['Required', 'Integer']]);
        $this->assertFalse($v->passes());
    }

    public function testValidateImplicitEachWithAsterisksForRequiredNonExistingKey()
    {
        

        $data = ['names' => [['second' => 'I have no first']]];
        $v = new Validator($data, ['names.*.first' => 'required']);
        $this->assertFalse($v->passes());

        $data = [];
        $v = new Validator($data, ['names.*.first' => 'required']);
        $this->assertTrue($v->passes());

        $data = ['names' => [['second' => 'I have no first']]];
        $v = new Validator($data, ['names.*.first' => 'required']);
        $this->assertFalse($v->passes());

        $data = ['people' => [
            ['cars' => [['model' => 2005], []]],
        ]];
        $v = new Validator($data, ['people.*.cars.*.model' => 'required']);
        $this->assertFalse($v->passes());

        $data = ['people' => [
            ['name' => 'test', 'cars' => [['model' => 2005], ['name' => 'test2']]],
        ]];
        $v = new Validator($data, ['people.*.cars.*.model' => 'required']);
        $this->assertFalse($v->passes());

        $data = ['people' => [
            ['phones' => ['iphone', 'android'], 'cars' => [['model' => 2005], ['name' => 'test2']]],
        ]];
        $v = new Validator($data, ['people.*.cars.*.model' => 'required']);
        $this->assertFalse($v->passes());

        $data = ['names' => [['second' => '2']]];
        $v = new Validator($data, ['names.*.first' => 'sometimes|required']);
        $this->assertTrue($v->passes());

        $data = [
            'people' => [
                ['name' => 'Jon', 'email' => 'a@b.c'],
                ['name' => 'Jon'],
            ],
        ];
        $v = new Validator($data, ['people.*.email' => 'required']);
        $this->assertFalse($v->passes());

        $data = [
            'people' => [
                [
                    'name' => 'Jon',
                    'cars' => [
                        ['model' => 2014],
                    ],
                ],
                [
                    'name' => 'Arya',
                    'cars' => [
                        ['name' => 'test'],
                    ],
                ],
            ],
        ];
        $v = new Validator($data, ['people.*.cars.*.model' => 'required']);
        $this->assertFalse($v->passes());
    }

    public function testValidateNestedArrayWithCommonParentChildKey()
    {
        

        $data = [
            'products' => [
                [
                    'price' => 2,
                    'options' => [
                        ['price' => 1],
                    ],
                ],
                [
                    'price' => 2,
                    'options' => [
                        ['price' => 0],
                    ],
                ],
            ],
        ];
        $v = new Validator($data, ['products.*.price' => 'numeric|min:1']);
        $this->assertTrue($v->passes());
    }

    public function testValidateNestedArrayWithNonNumericKeys()
    {
        

        $data = [
            'item_amounts' => [
                'item_123' => 2,
            ],
        ];

        $v = new Validator($data, ['item_amounts.*' => 'numeric|min:5']);
        $this->assertFalse($v->passes());
    }

    public function testValidateImplicitEachWithAsterisksConfirmed()
    {
        

        // confirmed passes
        $v = new Validator(['foo' => [
            ['password' => 'foo0', 'password_confirmation' => 'foo0'],
            ['password' => 'foo1', 'password_confirmation' => 'foo1'],
        ]], ['foo.*.password' => 'confirmed']);
        $this->assertTrue($v->passes());

        // nested confirmed passes
        $v = new Validator(['foo' => [
            ['bar' => [
                ['password' => 'bar0', 'password_confirmation' => 'bar0'],
                ['password' => 'bar1', 'password_confirmation' => 'bar1'],
            ]],
            ['bar' => [
                ['password' => 'bar2', 'password_confirmation' => 'bar2'],
                ['password' => 'bar3', 'password_confirmation' => 'bar3'],
            ]],
        ]], ['foo.*.bar.*.password' => 'confirmed']);
        $this->assertTrue($v->passes());

        // confirmed fails
        $v = new Validator(['foo' => [
            ['password' => 'foo0', 'password_confirmation' => 'bar0'],
            ['password' => 'foo1'],
        ]], ['foo.*.password' => 'confirmed']);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.password'));
        $this->assertTrue($v->messages()->has('foo.1.password'));

        // nested confirmed fails
        $v = new Validator(['foo' => [
            ['bar' => [
                ['password' => 'bar0'],
                ['password' => 'bar1', 'password_confirmation' => 'bar2'],
            ]],
        ]], ['foo.*.bar.*.password' => 'confirmed']);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.bar.0.password'));
        $this->assertTrue($v->messages()->has('foo.0.bar.1.password'));
    }

    public function testValidateImplicitEachWithAsterisksDifferent()
    {
        

        // different passes
        $v = new Validator(['foo' => [
            ['name' => 'foo', 'last' => 'bar'],
            ['name' => 'bar', 'last' => 'foo'],
        ]], ['foo.*.name' => ['different:foo.*.last']]);
        $this->assertTrue($v->passes());

        // nested different passes
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => 'foo', 'last' => 'bar'],
                ['name' => 'bar', 'last' => 'foo'],
            ]],
        ]], ['foo.*.bar.*.name' => ['different:foo.*.bar.*.last']]);
        $this->assertTrue($v->passes());

        // different fails
        $v = new Validator(['foo' => [
            ['name' => 'foo', 'last' => 'foo'],
            ['name' => 'bar', 'last' => 'bar'],
        ]], ['foo.*.name' => ['different:foo.*.last']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.name'));
        $this->assertTrue($v->messages()->has('foo.1.name'));

        // nested different fails
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => 'foo', 'last' => 'foo'],
                ['name' => 'bar', 'last' => 'bar'],
            ]],
        ]], ['foo.*.bar.*.name' => ['different:foo.*.bar.*.last']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.bar.0.name'));
        $this->assertTrue($v->messages()->has('foo.0.bar.1.name'));
    }

    public function testValidateImplicitEachWithAsterisksSame()
    {
        

        // same passes
        $v = new Validator(['foo' => [
            ['name' => 'foo', 'last' => 'foo'],
            ['name' => 'bar', 'last' => 'bar'],
        ]], ['foo.*.name' => ['same:foo.*.last']]);
        $this->assertTrue($v->passes());

        // nested same passes
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => 'foo', 'last' => 'foo'],
                ['name' => 'bar', 'last' => 'bar'],
            ]],
        ]], ['foo.*.bar.*.name' => ['same:foo.*.bar.*.last']]);
        $this->assertTrue($v->passes());

        // same fails
        $v = new Validator(['foo' => [
            ['name' => 'foo', 'last' => 'bar'],
            ['name' => 'bar', 'last' => 'foo'],
        ]], ['foo.*.name' => ['same:foo.*.last']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.name'));
        $this->assertTrue($v->messages()->has('foo.1.name'));

        // nested same fails
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => 'foo', 'last' => 'bar'],
                ['name' => 'bar', 'last' => 'foo'],
            ]],
        ]], ['foo.*.bar.*.name' => ['same:foo.*.bar.*.last']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.bar.0.name'));
        $this->assertTrue($v->messages()->has('foo.0.bar.1.name'));
    }

    public function testValidateImplicitEachWithAsterisksRequired()
    {
        

        // required passes
        $v = new Validator(['foo' => [
            ['name' => 'first'],
            ['name' => 'second'],
        ]], ['foo.*.name' => ['Required']]);
        $this->assertTrue($v->passes());

        // nested required passes
        $v = new Validator(['foo' => [
            ['name' => 'first'],
            ['name' => 'second'],
        ]], ['foo.*.name' => ['Required']]);
        $this->assertTrue($v->passes());

        // required fails
        $v = new Validator(['foo' => [
            ['name' => null],
            ['name' => null, 'last' => 'last'],
        ]], ['foo.*.name' => ['Required']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.name'));
        $this->assertTrue($v->messages()->has('foo.1.name'));

        // nested required fails
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => null],
                ['name' => null],
            ]],
        ]], ['foo.*.bar.*.name' => ['Required']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.bar.0.name'));
        $this->assertTrue($v->messages()->has('foo.0.bar.1.name'));
    }

    public function testValidateImplicitEachWithAsterisksRequiredIf()
    {
        

        // required_if passes
        $v = new Validator(['foo' => [
            ['name' => 'first', 'last' => 'foo'],
            ['last' => 'bar'],
        ]], ['foo.*.name' => ['Required_if:foo.*.last,foo']]);
        $this->assertTrue($v->passes());

        // nested required_if passes
        $v = new Validator(['foo' => [
            ['name' => 'first', 'last' => 'foo'],
            ['last' => 'bar'],
        ]], ['foo.*.name' => ['Required_if:foo.*.last,foo']]);
        $this->assertTrue($v->passes());

        // required_if fails
        $v = new Validator(['foo' => [
            ['name' => null, 'last' => 'foo'],
            ['name' => null, 'last' => 'foo'],
        ]], ['foo.*.name' => ['Required_if:foo.*.last,foo']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.name'));
        $this->assertTrue($v->messages()->has('foo.1.name'));

        // nested required_if fails
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => null, 'last' => 'foo'],
                ['name' => null, 'last' => 'foo'],
            ]],
        ]], ['foo.*.bar.*.name' => ['Required_if:foo.*.bar.*.last,foo']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.bar.0.name'));
        $this->assertTrue($v->messages()->has('foo.0.bar.1.name'));
    }

    public function testValidateImplicitEachWithAsterisksRequiredUnless()
    {
        

        // required_unless passes
        $v = new Validator(['foo' => [
            ['name' => null, 'last' => 'foo'],
            ['name' => 'second', 'last' => 'bar'],
        ]], ['foo.*.name' => ['Required_unless:foo.*.last,foo']]);
        $this->assertTrue($v->passes());

        // nested required_unless passes
        $v = new Validator(['foo' => [
            ['name' => null, 'last' => 'foo'],
            ['name' => 'second', 'last' => 'foo'],
        ]], ['foo.*.bar.*.name' => ['Required_unless:foo.*.bar.*.last,foo']]);
        $this->assertTrue($v->passes());

        // required_unless fails
        $v = new Validator(['foo' => [
            ['name' => null, 'last' => 'baz'],
            ['name' => null, 'last' => 'bar'],
        ]], ['foo.*.name' => ['Required_unless:foo.*.last,foo']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.name'));
        $this->assertTrue($v->messages()->has('foo.1.name'));

        // nested required_unless fails
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => null, 'last' => 'bar'],
                ['name' => null, 'last' => 'bar'],
            ]],
        ]], ['foo.*.bar.*.name' => ['Required_unless:foo.*.bar.*.last,foo']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.bar.0.name'));
        $this->assertTrue($v->messages()->has('foo.0.bar.1.name'));
    }

    public function testValidateImplicitEachWithAsterisksRequiredWith()
    {
        // required_with passes
        $v = new Validator(['foo' => [
            ['name' => 'first', 'last' => 'last'],
            ['name' => 'second', 'last' => 'last'],
        ]], ['foo.*.name' => ['Required_with:foo.*.last']]);
        $this->assertTrue($v->passes());

        // nested required_with passes
        $v = new Validator(['foo' => [
            ['name' => 'first', 'last' => 'last'],
            ['name' => 'second', 'last' => 'last'],
        ]], ['foo.*.name' => ['Required_with:foo.*.last']]);
        $this->assertTrue($v->passes());

        // required_with fails
        $v = new Validator(['foo' => [
            ['name' => null, 'last' => 'last'],
            ['name' => null, 'last' => 'last'],
        ]], ['foo.*.name' => ['Required_with:foo.*.last']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.name'));
        $this->assertTrue($v->messages()->has('foo.1.name'));

        // nested required_with fails
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => null, 'last' => 'last'],
                ['name' => null, 'last' => 'last'],
            ]],
        ]], ['foo.*.bar.*.name' => ['Required_with:foo.*.bar.*.last']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.bar.0.name'));
        $this->assertTrue($v->messages()->has('foo.0.bar.1.name'));
    }

    public function testValidateImplicitEachWithAsterisksRequiredWithAll()
    {
        // required_with_all passes
        $v = new Validator(['foo' => [
            ['name' => 'first', 'last' => 'last', 'middle' => 'middle'],
            ['name' => 'second', 'last' => 'last', 'middle' => 'middle'],
        ]], ['foo.*.name' => ['Required_with_all:foo.*.last,foo.*.middle']]);
        $this->assertTrue($v->passes());

        // nested required_with_all passes
        $v = new Validator(['foo' => [
            ['name' => 'first', 'last' => 'last', 'middle' => 'middle'],
            ['name' => 'second', 'last' => 'last', 'middle' => 'middle'],
        ]], ['foo.*.name' => ['Required_with_all:foo.*.last,foo.*.middle']]);
        $this->assertTrue($v->passes());

        // required_with_all fails
        $v = new Validator(['foo' => [
            ['name' => null, 'last' => 'last', 'middle' => 'middle'],
            ['name' => null, 'last' => 'last', 'middle' => 'middle'],
        ]], ['foo.*.name' => ['Required_with_all:foo.*.last,foo.*.middle']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.name'));
        $this->assertTrue($v->messages()->has('foo.1.name'));

        // nested required_with_all fails
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => null, 'last' => 'last', 'middle' => 'middle'],
                ['name' => null, 'last' => 'last', 'middle' => 'middle'],
            ]],
        ]], ['foo.*.bar.*.name' => ['Required_with_all:foo.*.bar.*.last,foo.*.bar.*.middle']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.bar.0.name'));
        $this->assertTrue($v->messages()->has('foo.0.bar.1.name'));
    }

    public function testValidateImplicitEachWithAsterisksRequiredWithout()
    {
        

        // required_without passes
        $v = new Validator(['foo' => [
            ['name' => 'first', 'middle' => 'middle'],
            ['name' => 'second', 'last' => 'last'],
        ]], ['foo.*.name' => ['Required_without:foo.*.last,foo.*.middle']]);
        $this->assertTrue($v->passes());

        // nested required_without passes
        $v = new Validator(['foo' => [
            ['name' => 'first', 'middle' => 'middle'],
            ['name' => 'second', 'last' => 'last'],
        ]], ['foo.*.name' => ['Required_without:foo.*.last,foo.*.middle']]);
        $this->assertTrue($v->passes());

        // required_without fails
        $v = new Validator(['foo' => [
            ['name' => null, 'last' => 'last'],
            ['name' => null, 'middle' => 'middle'],
        ]], ['foo.*.name' => ['Required_without:foo.*.last,foo.*.middle']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.name'));
        $this->assertTrue($v->messages()->has('foo.1.name'));

        // nested required_without fails
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => null, 'last' => 'last'],
                ['name' => null, 'middle' => 'middle'],
            ]],
        ]], ['foo.*.bar.*.name' => ['Required_without:foo.*.bar.*.last,foo.*.bar.*.middle']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.bar.0.name'));
        $this->assertTrue($v->messages()->has('foo.0.bar.1.name'));
    }

    public function testValidateImplicitEachWithAsterisksRequiredWithoutAll()
    {
        

        // required_without_all passes
        $v = new Validator(['foo' => [
            ['name' => 'first'],
            ['name' => null, 'middle' => 'middle'],
            ['name' => null, 'middle' => 'middle', 'last' => 'last'],
        ]], ['foo.*.name' => ['Required_without_all:foo.*.last,foo.*.middle']]);
        $this->assertTrue($v->passes());

        // required_without_all fails
        // nested required_without_all passes
        $v = new Validator(['foo' => [
            ['name' => 'first'],
            ['name' => null, 'middle' => 'middle'],
            ['name' => null, 'middle' => 'middle', 'last' => 'last'],
        ]], ['foo.*.name' => ['Required_without_all:foo.*.last,foo.*.middle']]);
        $this->assertTrue($v->passes());

        $v = new Validator(['foo' => [
            ['name' => null, 'foo' => 'foo', 'bar' => 'bar'],
            ['name' => null, 'foo' => 'foo', 'bar' => 'bar'],
        ]], ['foo.*.name' => ['Required_without_all:foo.*.last,foo.*.middle']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.name'));
        $this->assertTrue($v->messages()->has('foo.1.name'));

        // nested required_without_all fails
        $v = new Validator(['foo' => [
            ['bar' => [
                ['name' => null, 'foo' => 'foo', 'bar' => 'bar'],
                ['name' => null, 'foo' => 'foo', 'bar' => 'bar'],
            ]],
        ]], ['foo.*.bar.*.name' => ['Required_without_all:foo.*.bar.*.last,foo.*.bar.*.middle']]);
        $this->assertFalse($v->passes());
        $this->assertTrue($v->messages()->has('foo.0.bar.0.name'));
        $this->assertTrue($v->messages()->has('foo.0.bar.1.name'));
    }

    public function testValidateEachWithNonIndexedArray()
    {
        
        $data = ['foobar' => [
            ['key' => 'foo', 'value' => 5],
            ['key' => 'foo', 'value' => 10],
            ['key' => 'foo', 'value' => 16],
        ]];

        $v = new Validator($data, ['foo' => 'Array']);
        $v->each('foobar', ['key' => 'required', 'value' => 'numeric|min:6|max:14']);
        $this->assertFalse($v->passes());

        $v = new Validator($data, ['foo' => 'Array']);
        $v->each('foobar', ['key' => 'required', 'value' => 'numeric|min:4|max:16']);
        $this->assertTrue($v->passes());
    }

    public function testValidateEachWithNonArrayWithArrayRule()
    {
        
        $v = new Validator(['foo' => 'string'], ['foo' => 'Array']);
        $v->each('foo', ['min:7|max:13']);
        $this->assertFalse($v->passes());
    }

    public function testValidateEachWithNonArrayWithoutArrayRule()
    {
        
        $v = new Validator(['foo' => 'string'], ['foo' => 'numeric']);
        $v->each('foo', ['min:7|max:13']);
        $this->assertFalse($v->passes());
    }

    public function testInlineMessagesMayUseAsteriskForEachRules()
    {
    }
}
