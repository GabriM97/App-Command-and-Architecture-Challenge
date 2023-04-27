<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory(100)->create()->each(function (User $user) {
            $user->roles()->attach(Role::inRandomOrder()->first());
        });

        User::factory(30)->create()->each(function (User $user) {
            /** @var Collection $roles */
            $roles = Role::inRandomOrder()->get();
            $user->roles()->attach($roles->pop());
            $user->roles()->attach($roles->pop());
        });
    }
}
