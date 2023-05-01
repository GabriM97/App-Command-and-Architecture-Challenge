<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Concerns\InteractsWithIO;

class CommandOutput
{
    use InteractsWithIO;

    protected const DEFAULT_OUTPUT_FILE = 'no-name.txt';

    /**
     * @var string $tableStyle  the default style of the console output.
     */
    protected string $consoleTableStyle = 'compact';
    
    /**
     * Print the passed content to the console. Pass $headers to include column headers to the output.
     *
     * @param  mixed $content
     * @param  array $headers
     * @return void
     */
    public function printTable($content, array $headers = [])
    {
        $this->table($headers, $content, $this->consoleTableStyle);
    }

    /**
     * Store the content into a local file. The file gets created if missing, overridden otherwise.
     *
     * @param  string $filepath
     * @param  array $content
     * @param  array $headers
     * @param  string $separator
     * @param  string $defaultFilename
     * @return void
     */
    public function printFile(
        string $path,
        array $content,
        array $headers = [],
        string $separator = ' ',
        string $defaultFilename = self::DEFAULT_OUTPUT_FILE
    ): void {
        
        $filepath = $this->getFinalFilepath($path, $defaultFilename);

        if (File::exists($filepath)) {
            $confirm = $this->output->confirm('The file `' . $filepath . '` already exists and will be overrid. Do you want to continue?');
            if ($confirm === false) {
                $this->output->info('File not overridden. Content not saved to file.');
                return;
            }
        }

        File::ensureDirectoryExists(File::dirname($filepath));

        // $printableContent = $this->makeContentPrintable($content);

        File::put($filepath, json_encode($content));

        $this->output->info('Content saved to ' . $filepath);
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
     * Set the console output instance.
     * 
     * @return void
     */
    public function setOutput($output): void
    {
        $this->output = $output;
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