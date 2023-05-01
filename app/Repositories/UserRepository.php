<?php

namespace App\Repositories;

use App\Enums\Query;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{ 
    /**
     * Returns a collection of banned users. Use the parametes to customise the query.
     *
     * @return Collection
     */
    public function getBannedUsers(
        $columns = ['*'],
        $trashed = Query::Exclude,
        $admin = Query::Include,
        $active = Query::Include,
        $sortBy = 'email'
    ): Collection {

        $query = User::whereNotNull('banned_at');

        if ($trashed === Query::Include) {
            $query->withTrashed();
        }

        if ($trashed === Query::Only) {
            $query->onlyTrashed();
        }

        if ($admin === Query::Exclude) {
            $query->whereDoesntHave('roles', function (Builder $builder) {
                $builder->where('name', Role::ADMIN);
            });
        }

        if ($admin === Query::Only) {
            $query->whereHas('roles', function (Builder $builder) {
                $builder->where('name', Role::ADMIN);
            });
        }
        
        if ($active === Query::Only) {
            $query->whereNotNull('activated_at');
        }

        return 
            $query->orderBy($sortBy)
                ->get($columns)
                ->makeVisible('banned_at');
    }
}