<?php

namespace Tests\Console\Commands;

use Mockery;
use DateTime;
use Tests\TestCase;
use App\Enums\Query;
use App\Models\User;
use Mockery\MockInterface;
use App\Services\CommandOutput;
use App\Repositories\UserRepository;
use App\Console\Commands\GetBannedUsers;
use Illuminate\Database\Eloquent\Collection;
use App\Services\UsersCommandsOptionsResolver;
use App\Validators\GetBannedUsersInputValidator;
use App\Exceptions\CannotOverrideExistingFileException;

class GetBannedUsersTest extends TestCase
{
    /**
     * @var $validator a mock of the input validator
     */
    protected GetBannedUsersInputValidator|MockInterface $validator;
    
    /**
     * @var $resolver a mock of the options resolver
     */
    protected UsersCommandsOptionsResolver|MockInterface $resolver;

    /**
     * @var $repository a mock of the user repository
     */
    protected UserRepository|MockInterface $repository;

    /**
     * @var $output a mock of the command output
     */
    protected CommandOutput|MockInterface $output;

    /**
     * @var $command a partial mock of the banned-users:get command
     */
    protected GetBannedUsers|MockInterface $command;

    /**
     * @var array $defaultOptions the default command options
     */
    protected array $defaultOptions = [];

    /**
     * @var array $defaultArguments the default command arguments
     */
    protected array $defaultArguments = [];

    /**
     * @var array $resolvedOptions the default resolved options
     */
    protected array $resolvedOptions = [];

    /**
     * @var Collection $defaultUsers the default banned users collection
     */
    protected Collection $defaultUsers;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = Mockery::mock(GetBannedUsersInputValidator::class);
        $this->resolver = Mockery::mock(UsersCommandsOptionsResolver::class);
        $this->repository = Mockery::mock(UserRepository::class);
        $this->output = Mockery::mock(CommandOutput::class);
        
        // don't inject services in the constructor otherwise the parent constructor will be executed
        $this->command = Mockery::mock(GetBannedUsers::class)->makePartial();

        $this->command->setValidator($this->validator);
        $this->command->setOptionsResolver($this->resolver);
        $this->command->setUserRepository($this->repository);
        $this->command->setCommandOutput($this->output);

        $this->setupOptions();
        $this->setupArguments();
        $this->setupResolvedOptions();
        $this->setupDefaultUsers();
    }

    /**
     * @return void
     */
    protected function setupOptions(): void
    {
        $this->defaultOptions = [
            'active-users-only' => false,
            'with-trashed' => false,
            'with-headers' => false,
            'trashed-only' => false,
            'no-admin' => false,
            'admin-only' => false,
        ];
    }

    /**
     * @return void
     */
    protected function setupArguments(): void
    {
        $this->defaultArguments = [
            'save-to' => null,
            'sort-by' => 'email',
        ];
    }

    /**
     * @return void
     */
    protected function setupResolvedOptions(): void
    {
        $this->resolvedOptions = [
            'trashed' => Query::Exclude,
            'admin' => Query::Include,
            'active' => Query::Include,
            'sort-by' => 'email',
        ];
    }

    /**
     * @return void
     */
    protected function setupDefaultUsers(): void
    {
        $this->defaultUsers = Collection::make([
            new User([
                'id' => 1,
                'email' => 'test@test.com',
                'banned_at' => new DateTime()
            ]),
        ]);
    }
    

    /**
     * Test Get Banned Users command without custom options
     * 
     * @return void
     */
    public function testGetBannedUsersWithoutCustomOptions()
    {
        $this->command->shouldReceive('options')->once()->andReturn($this->defaultOptions);
        $this->command->shouldReceive('arguments')->once()->andReturn($this->defaultArguments);
        $this->command->shouldNotReceive(['confirm', 'info']);

        $this->validator->shouldReceive('validate')->once();

        $this->resolver->shouldReceive('resolve')->once()->andReturn($this->resolvedOptions);

        $this->repository->shouldReceive('getBannedUsers')->once()->andReturn($this->defaultUsers);
        
        $this->output->shouldReceive('setOutput')->once();
        $this->output->shouldReceive('printTable')->once()->with($this->defaultUsers, []);

        $this->output->shouldNotReceive('printFile');

        $this->command->setValidator($this->validator);
        $this->command->setOptionsResolver($this->resolver);
        $this->command->setUserRepository($this->repository);
        $this->command->setCommandOutput($this->output);

        $this->command->handle();
    }

    /**
     * Test Get Banned Users command with save-to option and confirm override
     *
     * @return void
     */
    public function testGetBannedUsersWithSaveToOptionAndConfirmOverride()
    {
        $this->defaultArguments['save-to'] = 'my/test/file.csv';

        $this->command->shouldReceive('options')->once()->andReturn($this->defaultOptions);
        $this->command->shouldReceive('arguments')->once()->andReturn($this->defaultArguments);

        $this->validator->shouldReceive('validate')->once()
            ->with(array_merge($this->defaultOptions, $this->defaultArguments));

        $this->resolvedOptions['save-to'] = $this->defaultArguments['save-to'];
        $this->resolver->shouldReceive('resolve')->once()
            ->with(array_merge($this->defaultOptions, $this->defaultArguments))
            ->andReturn($this->resolvedOptions);

        $this->repository->shouldReceive('getBannedUsers')->once()->andReturn($this->defaultUsers);
        
        $this->output->shouldReceive('setOutput')->once();
        $this->output->shouldReceive('printTable')->once()->withSomeOfArgs($this->defaultUsers);

        $this->output->shouldReceive('printFile')->once()
            ->andThrow(CannotOverrideExistingFileException::class, $this->defaultArguments['save-to']);

        $this->command->shouldReceive('confirm')->once()->andReturn(true);

        $this->command->shouldReceive('info')
            ->with('File not overridden. Content not saved to file.')
            ->never();

        $this->output->shouldReceive('printFile')->once()->andReturn($this->defaultArguments['save-to']);
        $this->command->shouldReceive('info')
            ->with('Content saved to `' . $this->defaultArguments['save-to'] . '`.')
            ->once();

        $this->command->setValidator($this->validator);
        $this->command->setOptionsResolver($this->resolver);
        $this->command->setUserRepository($this->repository);
        $this->command->setCommandOutput($this->output);

        $this->command->handle();
    }

    /**
     * Test Get Banned Users command with save-to option and don't confirm override
     *
     * @return void
     */
    public function testGetBannedUsersWithSaveToOptionAndDontConfirmOverride()
    {
        $this->defaultArguments['save-to'] = 'my/test/file.csv';

        $this->command->shouldReceive('options')->once()->andReturn($this->defaultOptions);
        $this->command->shouldReceive('arguments')->once()->andReturn($this->defaultArguments);

        $this->validator->shouldReceive('validate')->once()
            ->with(array_merge($this->defaultOptions, $this->defaultArguments));

        $this->resolvedOptions['save-to'] = $this->defaultArguments['save-to'];
        $this->resolver->shouldReceive('resolve')->once()
            ->with(array_merge($this->defaultOptions, $this->defaultArguments))
            ->andReturn($this->resolvedOptions);

        $this->repository->shouldReceive('getBannedUsers')->once()->andReturn($this->defaultUsers);
        
        $this->output->shouldReceive('setOutput')->once();
        $this->output->shouldReceive('printTable')->once()->withSomeOfArgs($this->defaultUsers);

        $this->output->shouldReceive('printFile')->once()
            ->andThrow(CannotOverrideExistingFileException::class, $this->defaultArguments['save-to']);

        $this->command->shouldReceive('confirm')->once()->andReturn(false);
        $this->command->shouldReceive('info')->once()->with('File not overridden. Content not saved to file.');

        $this->output->shouldNotReceive('printFile');
        $this->output->shouldNotReceive('info')->with('Content saved to `' . $this->defaultArguments['save-to'] . '`.');

        $this->command->setValidator($this->validator);
        $this->command->setOptionsResolver($this->resolver);
        $this->command->setUserRepository($this->repository);
        $this->command->setCommandOutput($this->output);

        $this->command->handle();
    }
}
