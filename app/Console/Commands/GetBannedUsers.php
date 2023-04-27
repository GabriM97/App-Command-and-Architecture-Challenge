<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GetBannedUsers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'banned-users:get {save-to?} {--active-users-only} {--with-trashed} {--trashed-only} {--no-admin} {--admin-only} {sort-by=email} {--with-headers}';

    /**
     * The console command description.
     */
    protected $description = 'Get banned users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = null;

        if($this->option('with-trashed')) {
           $query = User::withTrashed();
        } else if ($this->option('trashed-only')) {
            $query = User::onlyTrashed();
        } else {
            $query = User::withoutTrashed();
        }

        $query = $query->whereNotNull('banned_at');

        if($this->option('active-users-only')) {
            $query = $query->whereNull('activated_at');
        }

        if($this->option('no-admin')) {
            $query = $query->whereHas('roles', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->where('name', '<>', 'ADMIN');
            });
        }

        if($this->option('admin-only')) {
            $query = $query->whereHas('roles', function (\Illuminate\Database\Eloquent\Builder $builder) { $builder->where('name', 'admin'); });
        }

        $o = [];
        /** @var User $user */
        foreach($query->get() as $user) {
            $o[] = [
                'email' => $user->email,
                'banned_at' => $user->banned_at,
                'id' => $user->id,
            ];
        }
        $uu = '';
        foreach ($o as $u) {
            $uu .= $u['id']. ';'.$u['email'] . ";" . $u['banned_at'].PHP_EOL;
        }
        if($this->option('with-headers')) {
            echo 'id;email;banned_at';
        }
        echo ($uu);
        if($this->argument('save-to')) {
            file_put_contents(explode('=',$this->argument('save-to'))[1],$uu);
        }
    }
}
