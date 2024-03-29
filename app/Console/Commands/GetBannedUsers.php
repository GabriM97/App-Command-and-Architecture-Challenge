<?php

namespace App\Console\Commands;

use App\Services\CommandOutput;
use Illuminate\Console\Command;
use App\Repositories\UserRepository;
use App\Services\UsersCommandsOptionsResolver;
use App\Validators\GetBannedUsersInputValidator;
use App\Exceptions\CannotOverrideExistingFileException;

class GetBannedUsers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'banned-users:get 
                                {save-to? : The absolute filepath in which store the output.}
                                {sort-by=email : The field to use when sorting the output.}
                                {--active-users-only : Will only show banned users that have been previously activated.}
                                {--with-trashed : Will show banned users, including the users deleted.}
                                {--trashed-only : Will only show banned users that have been deleted.}
                                {--no-admin : Will show the banned users excluding the `admin` users.}
                                {--admin-only : Will only show the banned users that are `admin`.}
                                {--with-headers : Will print and save column headers.}';

    /**
     * The console command description.
     */
    protected $description = 'Get banned users.';

    public const COLUMN_HEADERS = ['id', 'email', 'banned_at'];

    protected const OUTPUT_FILE = 'banned_users.csv';

    protected const FILE_SEPARATOR = ';';

    /**
     * Instantiate the GetBannedUsers command.
     *
     * @param  GetBannedUsersInputValidator $validator  validates the user input
     * @param  UsersCommandsOptionsResolver $optionsResolver    resolves the options to process
     * @param  UserRepository $userRepository   retrieves the user data
     * @param  CommandOutput $commandOutput     prints the output
     * @return void
     */
    public function __construct(
        protected GetBannedUsersInputValidator $validator,
        protected UsersCommandsOptionsResolver $optionsResolver,
        protected UserRepository $userRepository,
        protected CommandOutput $commandOutput
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Validate options and arguments in input
        $input = array_merge($this->options(), $this->arguments());
        $this->validator->validate($input);

        // Resolve the given options
        $options = $this->optionsResolver->resolve($input);

        // Get banned users with options
        $bannedUsers = 
            $this->userRepository
                ->getBannedUsers(
                    self::COLUMN_HEADERS,
                    $options['trashed'],
                    $options['admin'],
                    $options['active'],
                    $options['sort-by']
                );

        // print output to CLI
        $headers = $input['with-headers'] ? self::COLUMN_HEADERS : [];
        $this->commandOutput->setOutput($this->output);
        $this->commandOutput->printTable($bannedUsers, $headers);
        
        // save output to file
        if ($path = $input['save-to']) {
            try {
                // try printing the output to file
                $filepath = 
                    $this->commandOutput->printFile(
                        $path, $bannedUsers, $headers, self::FILE_SEPARATOR, self::OUTPUT_FILE, true
                    );
            } catch (CannotOverrideExistingFileException $e) {
                // ask user if they want to override the file
                $confirm = $this->confirm($e->getMessage() . ' Do you want to override the file anyway?');

                if ($confirm === false) {
                    $this->info('File not overridden. Content not saved to file.');

                    return;
                }
                
                // print the output to file and enforce file overriding
                $filepath = 
                    $this->commandOutput->printFile(
                        $path, $bannedUsers, $headers, self::FILE_SEPARATOR, self::OUTPUT_FILE, true, true
                    );
            }
            $this->info('Content saved to `' . $filepath . '`.');
        }
    }

    /**
     * @param  $validator
     * @return void
     */
    public function setValidator ($validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param  $optionsResolver
     * @return void
     */
    public function setOptionsResolver ($optionsResolver)
    {
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * @param  $userRepository
     * @return void
     */
    public function setUserRepository ($userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param  $commandOutput
     * @return void
     */
    public function setCommandOutput ($commandOutput)
    {
        $this->commandOutput = $commandOutput;
    }
}
