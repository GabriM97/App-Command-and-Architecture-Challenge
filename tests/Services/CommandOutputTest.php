<?php

namespace Tests\Services;

use App\Exceptions\CannotOverrideExistingFileException;
use Mockery;
use Tests\TestCase;
use Mockery\MockInterface;
use App\Services\CommandOutput;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandOutputTest extends TestCase
{
    /**
     * @var $commandOutput a partial mock for the command output class
     */
    protected CommandOutput|MockInterface $commandOutput;

    /**
     * @var $content a collection of items to print
     */
    protected Collection $content;

    /**
     * @var $bufferedOutput a mock of the buffered output
     */
    protected BufferedOutput|MockInterface $bufferedOutput;

    /**
     * @var $output a mock of the console output
     */
    protected OutputStyle|MockInterface $output;

    /**
     * @var $headers the array of headers to print
     */
    protected array $headers = [];

    /**
     * @var $defaultFilename the default filename to use
     */
    protected string $defaultFilename = '';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->commandOutput = 
            Mockery::mock(CommandOutput::class)->makePartial()
                ->shouldAllowMockingProtectedMethods();

        $this->headers = ['id', 'name'];
        $this->defaultFilename = CommandOutput::DEFAULT_OUTPUT_FILE;

        $this->makeContent([$this->userAlias]);

        $this->bufferedOutput = Mockery::mock(BufferedOutput::class);
        $this->commandOutput->setBufferedOutput($this->bufferedOutput);
        
        $this->output = Mockery::mock(OutputStyle::class);
        $this->commandOutput->setOutput($this->output);
    }

    /**
     * @return void
     */
    protected function makeContent(array $content = [])
    {
        $this->content = Collection::make($content);
    }

    /**
     * Test Print Table calls "table" with passed headers and content
     * 
     * @return void
     */
    public function testPrintTableWithHeadersAndContent()
    {
        $this->commandOutput->shouldReceive('table')->once()
            ->withSomeOfArgs($this->headers, $this->content);

        $this->commandOutput->printTable($this->content, $this->headers);
    }

    /**
     * Test Print File writes to file and returns the final filepath
     * 
     * @return void
     */
    public function testPrintFileWritesToFileAndReturnsFinalFilepath()
    {
        $path = '/this/is/my/filepath';
        $filepath = $path . '/' . $this->defaultFilename;

        $this->commandOutput
            ->shouldReceive('getFinalFilepath')->once()->with($path, $this->defaultFilename)
            ->andReturn($filepath);

        File::shouldReceive('exists')->once()->with($filepath)->andReturn(false);
        File::shouldReceive('dirname')->once()->with($filepath)->andReturn(dirname($filepath));
        File::shouldReceive('ensureDirectoryExists')->once()->with(dirname($filepath));

        $this->commandOutput->shouldReceive('getFormattedContent')->once()
            ->withSomeOfArgs($this->content, $this->headers)
            ->andReturns(json_encode($this->content));

        File::shouldReceive('put')->once()->with($filepath, json_encode($this->content));

        $finalFilename= $this->commandOutput->printFile($path, $this->content, $this->headers);

        $this->assertEquals($filepath, $finalFilename);
    }

    /**
     * Test Print File writes to file and returns the final filepath when the file exists and the override is forced
     * 
     * @return void
     */
    public function testPrintFileWritesToFileAndReturnsFinalFilepathWhenFileExistsAndForcedOverride()
    {
        $path = '/this/is/my/filepath';
        $filepath = $path . '/' . $this->defaultFilename;

        $this->commandOutput
            ->shouldReceive('getFinalFilepath')->once()->with($path, $this->defaultFilename)
            ->andReturn($filepath);

        File::shouldReceive('exists')->once()->with($filepath)->andReturn(true);
        File::shouldReceive('dirname')->once()->with($filepath)->andReturn(dirname($filepath));
        File::shouldReceive('ensureDirectoryExists')->once()->with(dirname($filepath));

        $this->commandOutput->shouldReceive('getFormattedContent')->once()
            ->withSomeOfArgs($this->content, $this->headers)
            ->andReturns(json_encode($this->content));

        File::shouldReceive('put')->once()->with($filepath, json_encode($this->content));

        $forceOverride = true;
        $finalFilename= 
            $this->commandOutput->printFile(
                $path, $this->content, $this->headers, ' ', $this->defaultFilename, false, $forceOverride
            );

        $this->assertEquals($filepath, $finalFilename);
    }

    /**
     * Test Print File throws exception when the file exists and the override is NOT forced
     * 
     * @return void
     */
    public function testPrintFileThrowsExceptionWhenFileExistsAndNotForcedOverride()
    {
        $path = '/this/is/my/filepath';
        $filepath = $path . '/' . $this->defaultFilename;

        $this->commandOutput
            ->shouldReceive('getFinalFilepath')->once()->with($path, $this->defaultFilename)
            ->andReturn($filepath);

        File::shouldReceive('exists')->once()->with($filepath)->andReturn(true);

        File::shouldReceive('dirname')->never();
        File::shouldReceive('ensureDirectoryExists')->never();

        $this->commandOutput->shouldNotReceive('getFormattedContent');

        File::shouldReceive('put')->never();

        $this->expectException(CannotOverrideExistingFileException::class);

        $forceOverride = false;
        $this->commandOutput->printFile(
                $path, $this->content, $this->headers, ' ', $this->defaultFilename, false, $forceOverride
            );
    }

    /**
     * Test Get Final Filepath returns final path when path is a file and does not exist
     * 
     * @return void
     */
    public function testGetFinalFilepathReturnsFinalPathWhenPathIsFileAndNotExists()
    {
        $path = '/this/is/my/filepath/' . $this->defaultFilename;

        File::shouldReceive('isFile')->once()->with($path)->andReturn(true);
        File::shouldReceive('exists')->once()->with($path)->andReturn(false);
        File::shouldReceive('basename')->once()->with($path)->andReturn($this->defaultFilename);

        $final = $this->commandOutput->getFinalFilepath($path, $this->defaultFilename);

        $this->assertEquals($path, $final);
    }

    /**
     * Test Get Final Filepath returns final path when path is a file and does exist
     * 
     * @return void
     */
    public function testGetFinalFilepathReturnsFinalPathWhenPathIsFileAndExists()
    {
        $path = '/this/is/my/filepath/' . $this->defaultFilename;

        File::shouldReceive('isFile')->once()->with($path)->andReturn(true);
        File::shouldReceive('exists')->once()->with($path)->andReturn(true);
        File::shouldReceive('basename')->never();

        $final = $this->commandOutput->getFinalFilepath($path, $this->defaultFilename);

        $this->assertEquals($path, $final);
    }

    /**
     * Test Get Final Filepath returns final path when path is NOT a file and does NOT exist
     * 
     * @return void
     */
    public function testGetFinalFilepathReturnsFinalPathWhenPathIsNotFileAndNotExists()
    {
        $path = '/this/is/a/directory/path';

        File::shouldReceive('isFile')->once()->with($path)->andReturn(false);
        File::shouldReceive('exists')->once()->with($path)->andReturn(false);
        File::shouldReceive('basename')->once()->with($path)->andReturn('path');

        $final = $this->commandOutput->getFinalFilepath($path, $this->defaultFilename);

        $this->assertEquals($path . '/' . $this->defaultFilename, $final);
    }

    /**
     * Test Get Final Filepath returns final path when path is NOT a file and does exist
     * 
     * @return void
     */
    public function testGetFinalFilepathReturnsFinalPathWhenPathIsNotFileAndExists()
    {
        $path = '/this/is/a/directory/path';

        File::shouldReceive('isFile')->once()->with($path)->andReturn(false);
        File::shouldReceive('exists')->once()->with($path)->andReturn(true);
        File::shouldReceive('basename')->never();

        $final = $this->commandOutput->getFinalFilepath($path, $this->defaultFilename);

        $this->assertEquals($path . '/' . $this->defaultFilename, $final);
    }

    /**
     * Test Get Formatted Content returns the formatted content without removing the empty spaces
     * 
     * @return void
     */
    public function testGetFormattedContentReturnsFormattedContentWithoutRemovingSpaces()
    {
        $separator = ';';

        $this->userAlias->shouldReceive('getAttributes')->once()->andReturn($this->headers);
        $this->makeContent([$this->userAlias]);

        $this->bufferedOutput->shouldReceive('fetch')->once()->withNoArgs()
            ->andReturn(json_encode($this->content));

        $this->commandOutput->setBufferedOutput($this->bufferedOutput);

        $this->commandOutput->shouldReceive('setOutput')->once()->with($this->bufferedOutput)
            ->shouldReceive('initFileColumnsStyle')->once()->with(count($this->headers), $separator)
            ->shouldReceive('table')->once()->withSomeOfArgs($this->headers, $this->content)
            ->shouldReceive('getOutput')->once()->withNoArgs()->andReturn($this->bufferedOutput)
            ->shouldReceive('setOutput')->once()->with($this->output);
            
        $removeEmptySpaces = true;
        $finalContent = $this->commandOutput->getFormattedContent($this->content, $separator, $this->headers, $removeEmptySpaces);

        $this->assertEquals(json_encode($this->content), $finalContent);
    }

    /**
     * Test Get Formatted Content returns the formatted content removing the empty spaces
     * 
     * @return void
     */
    public function testGetFormattedContentReturnsFormattedContentRemovingSpaces()
    {
        $separator = ';';

        $this->userAlias->shouldReceive('getAttributes')->once()->andReturn($this->headers);
        $this->makeContent([$this->userAlias]);

        $this->bufferedOutput->shouldReceive('fetch')->once()->withNoArgs()
            ->andReturn(json_encode($this->content));

        $this->commandOutput->setBufferedOutput($this->bufferedOutput);

        $this->commandOutput->shouldReceive('setOutput')->once()->with($this->bufferedOutput)
            ->shouldReceive('initFileColumnsStyle')->once()->with(count($this->headers), $separator)
            ->shouldReceive('table')->once()->withSomeOfArgs($this->headers, $this->content)
            ->shouldReceive('getOutput')->once()->withNoArgs()->andReturn($this->bufferedOutput)
            ->shouldReceive('setOutput')->once()->with($this->output);
            
        $removeEmptySpaces = true;
        $finalContent = $this->commandOutput->getFormattedContent($this->content, $separator, $this->headers, $removeEmptySpaces);

        $this->assertEquals(str_replace(' ', '', json_encode($this->content)), $finalContent);
    }
}