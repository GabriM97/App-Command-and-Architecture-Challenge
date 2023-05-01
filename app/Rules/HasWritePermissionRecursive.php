<?php

namespace App\Rules;

use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Validation\InvokableRule;

class HasWritePermissionRecursive implements InvokableRule
{
    /**
     * @var string $message The message to print in case of failed validation
     */
    protected string $message = 'The path `%s` nor the first existing parent directory is writable.';

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
        if (!$this->isWritableRecursive($value)) {
            $fail(sprintf($this->message, $value, dirname($value)));
        }
    }

    /**
     * Check recursively if the first existing dir or file is writable.
     *
     * @param  string  $path
     * @return bool
     */
    protected function isWritableRecursive(string $path)
    {
        // base case
        if (in_array($path, ['/', '.', '..']) || File::exists($path)) {
            return File::isWritable($path);
        }

        return $this->isWritableRecursive(dirname($path));
    }
}