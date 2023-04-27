<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    private const TABLE_NAME = 'roles';

    public const ADMIN_ROLE = 'admin';
    public const STAFF_ROLE = 'staff';
    public const CUSTOMER_ROLE = 'customer';

    private const ROLES_TO_SEED = [self::ADMIN_ROLE, self::STAFF_ROLE, self::CUSTOMER_ROLE];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $table = DB::table(self::TABLE_NAME);

        if($table->count()) {
            return;
        }

        $roles = [];
        $now = Carbon::now();
        foreach (self::ROLES_TO_SEED as $toSeed) {
            $roles[] = ['name' => $toSeed, 'created_at' => $now, 'updated_at' => $now];
        }

        DB::table('roles')->insert($roles);
    }
}
