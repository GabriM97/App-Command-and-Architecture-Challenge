<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\BufferedOutput;
use App\Exceptions\CannotOverrideExistingFileException;

class CommandOutput
{
    use InteractsWithIO;

    public const DEFAULT_OUTPUT_FILE = 'no-name.txt';

    /**
     * @var string $consoleTableStyle  the default table style of the console output.
     */
    protected string $consoleTableStyle = 'compact';

    /**
     * @var string $fileTableStyle  the default table style for the file output.
     */
    protected string $fileTableStyle = 'compact';
    
    /**
     * @var array $fileColumnsStyle  the default columns style for the file output.
     */
    protected array $fileColumnsStyle = [];

    /**
     * @param BufferedOutput $bufferedOutput console buffered output to be used as hack for file formatting.
     */
    public function __construct(protected BufferedOutput $bufferedOutput)
    {
        // TODO: Consider replacing the hacky BufferedOutput in favour of a CSV Formatter

        $this->initFileColumnsStyle();
    }
    
    /**
     * Print the passed content to the console. Pass $headers to include column headers to the output.
     *
     * @param  Collection $content
     * @param  array $headers
     * @return void
     */
    public function printTable(Collection $content, array $headers = [])
    {
        $this->table($headers, $content, $this->consoleTableStyle);
    }

    /**
     * Store the content into a local file. The file gets created if missing, overridden otherwise.
     *
     * @param  string $filepath
     * @param  Collection $content
     * @param  array $headers
     * @param  string $separator
     * @param  string $defaultFilename
     * @param  bool $removeEmptySpaces
     * @param  bool $forceOverride
     * @return string
     */
    public function printFile(
        string $path,
        Collection $content,
        array $headers = [],
        string $separator = ' ',
        string $defaultFilename = self::DEFAULT_OUTPUT_FILE,
        bool $removeEmptySpaces = false,
        bool $forceOverride = false
    ): string {
        
        // get the final filepath appending the default filename if $path is a dir
        $filepath = $this->getFinalFilepath($path, $defaultFilename);

        // check for file existance and throw exception if file can't be overridden
        if (File::exists($filepath) && $forceOverride === false) {
            throw new CannotOverrideExistingFileException($filepath);
        }

        // create all the non-existing directories
        File::ensureDirectoryExists(File::dirname($filepath));

        // get the content as a formatted string
        $formattedContent = $this->getFormattedContent($content, $separator, $headers, $removeEmptySpaces);

        // write the content to file
        File::put($filepath, $formattedContent);

        return $filepath;
    }

    /**
     * Returns the final filepath, either the original one or the original concatenated to the default filename.
     *
     * @param  string $path
     * @param  string $defaultFilename
     * @return string
     */
    protected function getFinalFilepath(string $path, string $defaultFilename): string
    {
        $isFile = File::isFile($path);

        if (!File::exists($path)) {
            $basename = File::basename($path);
            $parts = explode('.', $basename);
            $isFile = count($parts) > 1 && !empty($parts[0]);
        }

        if ($isFile) {
            return $path;
        }

        return rtrim($path, '/') . '/' . $defaultFilename;
    }
    
    /**
     * Init the default table styling to use for each column in the file output.
     *
     * @param  int $columns
     * @param  string $delimiter
     * @return void
     */
    protected function initFileColumnsStyle(int $columns = 1, string $delimiter = '')
    {
        $this->fileColumnsStyle = Collection::times($columns, function () use ($delimiter) {
            return (new TableStyle())
                ->setHorizontalBorderChars('')
                ->setVerticalBorderChars('')
                ->setDefaultCrossingChar('')
                // ->setPaddingChar('')     // cannot set padding char to empty string
                ->setCellRowContentFormat('%s' . $delimiter);
        })->toArray();
    }
    
    /**
     * Format the content into a string and return it.
     *
     * @param  Collection $content
     * @param  string $separator
     * @param  array $headers
     * @param  bool $removeEmptySpaces
     * @return string
     */
    protected function getFormattedContent(
        Collection $content,
        string $separator,
        array $headers,
        bool $removeEmptySpaces = true
    ): string {
        
        // save the current console output instance
        $currentOutput = $this->output;

        // set the output to the buffered instance
        $this->setOutput($this->bufferedOutput);

        // init the file column style for each attribute in the collection
        $this->initFileColumnsStyle(count($content->first()->getAttributes()), $separator);

        // "draw" the table to the buffered output
        $this->table($headers, $content, $this->fileTableStyle, $this->fileColumnsStyle);

        // fetch the table in string format from the buffered output
        $formattedContent = $this->getOutput()->fetch();
        
        if ($removeEmptySpaces) {
            // hack to quickly remove cells padding
            // remove any whitespace char - note this will also remove whitespaces from cells contents
            $formattedContent = str_replace(' ', '', $formattedContent);
        }

        // restore the old output instance
        $this->setOutput($currentOutput);

        return $formattedContent;
    }

    /**
     * Set the console output instance.
     * 
     * @return void
     */
    public function setOutput($output): void
    {
        $this->output = $output;
    }

    /**
     * Set the buffered output instance.
     * 
     * @return void
     */
    public function setBufferedOutput($bufferedOutput): void
    {
        $this->bufferedOutput = $bufferedOutput;
    }

    /**
     * Set the console table style.
     * 
     * @return void
     */
    public function setConsoleTableStyle($style): void
    {
        $this->consoleTableStyle = $style;
    }
}