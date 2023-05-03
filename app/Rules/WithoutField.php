<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;

class WithoutField implements DataAwareRule, InvokableRule
{
    /**
     * @var mixed $data The data under validation 
     */
    protected $data = [];

    /**
     * @var string $message The message to print in case of failed validation
     */
    protected string $message = 'The option `%s` is only allowed without the `%s` option.';

    /**
     * Instantiate the Without rule.
     *
     * @param  string $without  the other parameter that can't live with the validated attribute
     * @return void
     */
    public function __construct(protected string $without)
    {
        
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail): void
    {
        if (!empty($value) && !empty($this->data[$this->without])) {
            // fails only when both attributes are present
            $fail(sprintf($this->message, $attribute, $this->without));
        }
    }

    /**
     * Set the data under validation.
     * 
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}