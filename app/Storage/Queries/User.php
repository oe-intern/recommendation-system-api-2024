<?php

namespace App\Storage\Queries;

use App\Contracts\Queries\User as UserQuery;
use App\Objects\Values\UserDomain;
use App\Objects\Values\UserId;
use Illuminate\Support\Collection;
use App\Models\User as UserModel;

class User implements UserQuery
{
    /**
     * The user model (configurable).
     *
     */
    protected UserModel $model;

    /**
     * Setup.
     *
     * @return void
     */
    public function __construct()
    {
        $this->model = app(UserModel::class);
    }
    /**
     * @inheritdoc
     */
    public function getById(UserId $userId, array $with = [], bool $withTrashed = false)
    {
        $result = $this->model::with($with);

        if ($withTrashed) {
            $result = $result->withTrashed();
        }

        return $result
            ->where('id', $userId->toNative())
            ->first();
    }

    /**
     * @inheritdoc
     */
    public function getByDomain(UserDomain $domain, array $with = [], bool $withTrashed = false)
    {
        $result = $this->model::with($with);

        if ($withTrashed) {
            $result = $result->withTrashed();
        }

        return $result
            ->where('name', $domain->toNative())
            ->first();
    }

    /**
     * @inheritdoc
     */
    public function getAll(array $with = []): Collection
    {
        return $this->model::with($with)->get();
    }
}
