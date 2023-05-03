<?php

namespace Tests\Validators;

use App\Validators\GetBannedUsersInputValidator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as IlluminateValidator;
use Mockery;
use Tests\TestCase;
use Mockery\MockInterface;

class GetBannedUsersInputValidatorTest extends TestCase
{
    /**
     * @var $validator the validator instance to test
     */
    protected GetBannedUsersInputValidator $validator;

    /**
     * @var $illuminateValidator a partial mock of the the illuminate validator
     */
    protected IlluminateValidator|MockInterface $illuminateValidator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->app->make(GetBannedUsersInputValidator::class);

        $this->illuminateValidator = Mockery::mock(IlluminateValidator::class)->makePartial();
    }

    /**
     * Test Get Banned Users using the default arguments
     * 
     * @return void
     */
    public function testValidateUsesIlluminateValidatorToValidate()
    {
        $input = [
            'no-admin' => true,
            'trashed-only' => false,
        ];

        $this->illuminateValidator->shouldReceive('validate')->once()->withNoArgs();

        Validator::shouldReceive('make')->once()->withSomeOfArgs($input)
            ->andReturn($this->illuminateValidator);
            
        $this->validator->validate($input);
    }
}