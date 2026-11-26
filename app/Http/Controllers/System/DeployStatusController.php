<?php

namespace App\Http\Controllers\System;

use App\Services\DeployStatusService;
use Illuminate\View\View;

class DeployStatusController
{
    public function __invoke(DeployStatusService $status): View
    {
        $username = strtolower((string) auth()->user()?->username);
        abort_unless(in_array($username, config('cutboard.manager_usernames', ['manager']), true), 403);

        $deployed = $status->deployed();
        $github = $status->githubRuns();
        $migrations = $status->migrations();

        return view('system.deploy', [
            'deployed' => $deployed,
            'localHead' => $status->localHead(),
            'github' => $github,
            'migrations' => $migrations,
            'env' => $status->environment(),
            'verdict' => $status->verdict($deployed, $github, $migrations),
        ]);
    }
}
