<?php

namespace App\Validators;

use Illuminate\Validation\Rule;
use App\Console\Commands\GetBannedUsers;
use App\Rules\WithoutField;
use App\Rules\HasWritePermissionRecursive;
use Illuminate\Support\Facades\Validator;

class GetBannedUsersInputValidator
{
    /**
     * Validate the given user's input for the banned-users:get command using the specified rules.
     *
     * @param  array $input
     * @return void
     */
    public function validate(array $input): void
    {
        Validator::make(
            $input, 
            [
                'no-admin' => new WithoutField('admin-only'),
                'admin-only' => new WithoutField('no-admin'),

                'with-trashed' => new WithoutField('trashed-only'),
                'trashed-only' => new WithoutField('with-trashed'),

                'sort-by' => ['required', Rule::in(GetBannedUsers::COLUMN_HEADERS)],
                'save-to' => ['nullable', new HasWritePermissionRecursive],
            ],
            [
                'in' => 'The :attribute field must be one of: ' . implode(', ', GetBannedUsers::COLUMN_HEADERS)
            ]
        )->validate();
    }
}