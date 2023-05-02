<?php

namespace App\Services;

use App\Enums\Query;
use App\Exceptions\IncompatibleOptionsException;

class UsersCommandsOptionsResolver
{
    /**
     * Given the options in input, resolve them per kind.
     *
     * @param  array $options
     * @return array
     * @throws IncompatibleOptionsException
     */
    public function resolve(array $options): array
    {
        if ($options['with-trashed'] && $options['trashed-only']) {
            throw new IncompatibleOptionsException('with-trashed', 'trashed-only');
        }

        if ($options['no-admin'] && $options['admin-only']) {
            throw new IncompatibleOptionsException('no-admin', 'admin-only');
        }

        // default resolution
        $resolvedOptions = [
            'trashed' => Query::Exclude,
            'admin' => Query::Include,
            'active' => Query::Include,
            'sort-by' => $options['sort-by']
        ];

        // Trashed option
        if ($options['with-trashed']) {
            $resolvedOptions['trashed'] = Query::Include;
        }
        if ($options['trashed-only']) {
            $resolvedOptions['trashed'] = Query::Only;
        }

        // Admin option
        if ($options['no-admin']) {
            $resolvedOptions['admin'] = Query::Exclude;
        }
        if ($options['admin-only']) {
            $resolvedOptions['admin'] = Query::Only;
        }

        // Active option
        if ($options['active-users-only']) {
            $resolvedOptions['active'] = Query::Only;
        }

        return $resolvedOptions;
    }
}