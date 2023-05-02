<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class IncompatibleOptionsException extends Exception
{    
    /**
     * @var string $customeMessage
     */
    protected string $customeMessage = 'The passed options `%s` and `%s` are incompatible. Please only pass one of them.';

    /**
     * Instantiate incompatible options exception. 
     *
     * @param 
     * @return void
     */
    public function __construct(
        protected string $optionOne,
        protected string $optionTwo,
        int $code = 0, 
        Throwable|null $previous = null
    ) {
        parent::__construct(
            sprintf($this->customeMessage, $this->optionOne, $this->optionTwo),
            $code,
            $previous
        );
    }
    
    /**
     * Return the first incompatible option.
     *
     * @return string
     */
    public function getOptionOne(): string
    {
        return $this->optionOne;
    }
    
    /**
     * Return the second incompatible option.
     *
     * @return string
     */
    public function getOptionTwo(): string
    {
        return $this->optionTwo;
    }
}