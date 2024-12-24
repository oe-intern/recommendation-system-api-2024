<?php

namespace App\Contracts\Queries;

use App\Objects\Values\UserDomain;
use App\Objects\Values\UserId;
use Illuminate\Support\Collection;

interface User
{
    /**
     * Get by ID.
     */
    public function getById(UserId $userId, array $with = [], bool $withTrashed = false);

    /**
     * Get by domain.
     */
    public function getByDomain(UserDomain $domain, array $with = [], bool $withTrashed = false);

    /**
     * Get all records.
     */
    public function getAll(array $with = []): Collection;
}
