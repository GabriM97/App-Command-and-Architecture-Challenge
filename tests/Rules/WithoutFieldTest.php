<?php

namespace Tests\Rules;

use Mockery;
use Tests\TestCase;
use App\Rules\WithoutField;

class WithoutFieldTest extends TestCase
{
    /**
     * @var $rule a partial mock for the rule to test
     */
    protected WithoutField $rule;

    /**
     * Test class is invoked correctly and the fail function is called when both options are true
     * 
     * @return void
     */
    public function testClassIsInvokedAndFailFunctionIsCalledWhenBothOptionsAreTrue()
    {
        $optionOne = 'option-one';
        $optionTwo = 'option-two';
        $this->rule = $this->app->make(WithoutField::class, ['without' => $optionTwo]);

        $failMessage = '';
        $fail = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $data = [
            $optionOne => true,
            $optionTwo => true,
        ];

        $this->rule->setData($data);

        // invoke the rule
        call_user_func($this->rule, $optionOne, $data[$optionOne], $fail);

        // pass test when the closure is called and the message contains both option names
        $this->assertTrue(
            !empty($failMessage)
            && str_contains($failMessage, $optionOne)
            && str_contains($failMessage, $optionTwo)
        );
    }

    /**
     * Test class is invoked correctly and the fail function is NOT called when both options are true
     * 
     * @return void
     */
    public function testClassIsInvokedAndFailFunctionIsNotCalledWhenOneOptionIsFalse()
    {
        $optionOne = 'option-one';
        $optionTwo = 'option-two';
        $this->rule = $this->app->make(WithoutField::class, ['without' => $optionTwo]);

        $failMessage = '';
        $fail = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $data = [
            $optionOne => true,
            $optionTwo => false,
        ];

        $this->rule->setData($data);

        // invoke the rule
        call_user_func($this->rule, $optionOne, $data[$optionOne], $fail);

        // pass test when the closure is NOT called and the message is empty
        $this->assertTrue(empty($failMessage));
    }

}