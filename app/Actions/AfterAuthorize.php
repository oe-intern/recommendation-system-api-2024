<?php

namespace App\Actions;

use App\Contracts\Queries\User as UserQuery;
use App\Models\User as UserModel;
use App\Objects\Values\UserId;
use Illuminate\Support\Arr;

class AfterAuthorize
{
    /**
     * Queries for shops.
     *
     * @var UserQuery
     */
    protected UserQuery $userQuery;

    /**
     * Setup.
     *
     * @param UserQuery $userQuery
     */
    public function __construct(UserQuery $userQuery)
    {
        $this->userQuery = $userQuery;
    }

    /**
     * Handle the action.
     *
     * @param UserId $id
     * @return void
     */
    public function __invoke(UserId $id): void
    {
        $user = $this->userQuery->getById($id);
        $jobs = config('shopify-app.after_authenticate_jobs', []);

        $this->processJobs($jobs, $user);
    }

    /**
     * Process the jobs.
     *
     * @param array $jobs
     * @param UserModel $user
     * @return void
     */
    private function processJobs(array $jobs, UserModel $user): void
    {
        foreach ($jobs as $job) {
            $class = Arr::get($job, 'class');
            $inline = Arr::get($job, 'inline', false);
            if ($inline) {
                $class::dispatchSync($user);
                continue;
            }

            $class::dispatch($user)
                ->onQueue(config('shopify-app.job_queues.after_authenticate'));
        }
    }
}
