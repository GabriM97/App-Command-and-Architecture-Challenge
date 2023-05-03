<?php

namespace Tests\Rules;

use Mockery;
use Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Support\Facades\File;
use App\Rules\HasWritePermissionRecursive;

class HasWritePermissionRecursiveTest extends TestCase
{
    /**
     * @var $fileMock a partial mock for the File facade
     */
    protected File|MockInterface $fileMock;

    /**
     * @var $rule a partial mock for the rule to test
     */
    protected HasWritePermissionRecursive|MockInterface $rule;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->rule = 
            Mockery::mock(HasWritePermissionRecursive::class)->makePartial()
                ->shouldAllowMockingProtectedMethods();

        $this->fileMock = File::partialMock();
    }

    /**
     * Test class is invoked correctly and the fail function is called when the path is not writable
     * 
     * @return void
     */
    public function testClassIsInvokedAndFailFunctionIsCalledWhenPathIsNotWritable()
    {
        $path = 'this/is/a/file/path';

        $this->rule->shouldReceive('isWritableRecursive')->once()->with($path)->andReturn(false);

        $failMessage = '';
        $fail = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        // invoke the rule
        call_user_func($this->rule, 'attribute', $path, $fail);

        // pass test when the closure is called and the message contains the non-writable path
        $this->assertTrue(!empty($failMessage) && str_contains($failMessage, $path));
    }

    /**
     * Test is class is invoked correctly and the fail function is NOT called when the path is writable
     * 
     * @return void
     */
    public function testClassIsInvokedAndFailFunctionIsNotCalledWhenPathIsWritable()
    {
        $path = 'this/is/a/file/path';

        $this->rule->shouldReceive('isWritableRecursive')->once()->with($path)->andReturn(true);

        $failMessage = '';
        $fail = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        // invoke the rule
        call_user_func($this->rule, 'attribute', $path, $fail);

        // pass test when the closure is NOT called and the message is empty
        $this->assertTrue(empty($failMessage));
    }

    /**
     * Test is writable recursive returns true when base case is not writable
     * 
     * @return void
     */
    public function testIsWritableRecursiveReturnsTrueWhenBaseCaseIsWritable()
    {
        $this->fileMock
            ->shouldReceive('exists')->once()->with('/this/loops/five/times/writable')->andReturn(false)
            ->shouldReceive('exists')->once()->with('/this/loops/five/times')->andReturn(false)
            ->shouldReceive('exists')->once()->with('/this/loops/five')->andReturn(false)
            ->shouldReceive('exists')->once()->with('/this/loops')->andReturn(false)
            ->shouldReceive('exists')->once()->with('/this')->andReturn(false)
            ->shouldNotReceive('exists')->with(['/', '.', '..'])
            ->shouldReceive('isWritable')->once()->with('/')->andReturn(true);

        $path = '/this/loops/five/times/writable';
        $isWritable = $this->rule->isWritableRecursive($path);

        $this->assertTrue($isWritable);
    }

    /**
     * Test is writable recursive returns false when base case is not writable
     * 
     * @return void
     */
    public function testIsWritableRecursiveReturnsFalseWhenBaseCaseIsNotWritable()
    {
        $this->fileMock
            ->shouldReceive('exists')->times(5)->andReturn(false)
            ->shouldNotReceive('exists')->with(['/', '.', '..'])
            ->shouldReceive('isWritable')->once()->with('/')->andReturn(false);

        $path = '/this/loops/five/times/non-writable';
        $isWritable = $this->rule->isWritableRecursive($path);

        $this->assertFalse($isWritable);
    }

    /**
     * Test is writable recursive returns when using a relative path
     * 
     * @return void
     */
    public function testIsWritableRecursiveReturnsWhenUsingRelativePath()
    {
        $this->fileMock
            ->shouldReceive('exists')->times(5)->andReturn(false)
            ->shouldNotReceive('exists')->with(['/', '.', '..'])
            ->shouldReceive('isWritable')->once()->with('.')->andReturn(false);

        $path = 'this/is/a/relative/path';
        $isWritable = $this->rule->isWritableRecursive($path);

        // don't need to check the actual value, but just make sure the function
        // returned correctly and we don't fall into an infinite loop.
        $this->assertIsBool($isWritable);
    }
}