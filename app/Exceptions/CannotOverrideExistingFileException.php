<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class CannotOverrideExistingFileException extends Exception
{
    /**
     * @var string $customeMessage
     */
    protected string $customeMessage = 'Cannot override existing file %s.';

    /**
     * Instantiate incompatible options exception. 
     *
     * @param 
     * @return void
     */
    public function __construct(
        protected string $filename,
        int $code = 0, 
        Throwable|null $previous = null
    ) {
        parent::__construct(sprintf($this->customeMessage, $this->filename), $code, $previous);
    }

    /**
     * Return the filename.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}