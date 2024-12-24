<?php

namespace App\Storage\Commands;

use App\Contracts\Commands\User as UserCommand;
use App\Contracts\Objects\Values\AccessToken;
use App\Objects\Values\UserDomain;
use App\Objects\Values\UserId;
use App\Contracts\Queries\User as UserQuery;
use App\Models\User as UserModel;

class User implements UserCommand
{
    /**
     * The User model
     */
    protected $model;

    /**
     * The query for Users.
     *
     * @var UserQuery
     */
    protected UserQuery $query;

    /**
     * Init for shop command.
     */
    public function __construct(UserQuery $query)
    {
        $this->query = $query;
        $this->model = app(UserModel::class);
    }

    /**
     * Create a User.
     *
     * @param UserDomain $domain
     * @param AccessToken $token
     * @return UserId
     */
    public function make(UserDomain $domain, AccessToken $token): UserId
    {
        $user = $this->model;
        $user->name = $domain->toNative();
        $user->password = $token->isNull() ? '' : $token->toNative();
        $user->email = "shop@{$domain->toNative()}";
        $user->save();

        return $user->getId();
    }

    /**
     * Set the access token for a user.
     *
     * @param UserId $id
     * @param AccessToken $token
     * @return UserId
     */
    public function setAccessToken(UserId $id, AccessToken $token): UserId
    {
        $user = $this->get($id);
        $user->password = $token->toNative();
        $user->password_updated_at = now();
        $user->save();

        return $user->getId();
    }

    /**
     * Helpers to get the shop.
     *
     */
    protected function get(UserId $id, bool $withTrashed = false): ?UserModel
    {
        return $this->query->getById($id, [], $withTrashed);
    }

    /**
     * @inheritdoc
     */
    public function softDelete(UserId $id): bool
    {
        $shop = $this->get($id);

        return $shop->delete();
    }
}
