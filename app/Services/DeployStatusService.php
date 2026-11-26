<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Everything needed to answer "is this server running the latest deploy, and is it healthy?":
 * the commit written by the GitHub Actions deploy, the latest workflow runs, pending migrations
 * (the IIS deploy does not run them) and cache / asset state.
 */
class DeployStatusService
{
    public function deployed(): ?array
    {
        $path = storage_path('app/deploy-info.json');
        if (! File::exists($path)) {
            return null;
        }
        $info = json_decode((string) File::get($path), true);

        return is_array($info) && ! empty($info['sha']) ? $info : null;
    }

    /** Commit checked out in a local working copy (dev machines have .git, the IIS copy does not). */
    public function localHead(): ?string
    {
        $head = base_path('.git/HEAD');
        if (! File::exists($head)) {
            return null;
        }
        $ref = trim((string) File::get($head));
        if (! str_starts_with($ref, 'ref: ')) {
            return $ref ?: null;
        }
        $ref = substr($ref, 5);
        $file = base_path('.git/'.$ref);
        if (File::exists($file)) {
            return trim((string) File::get($file)) ?: null;
        }
        $packed = base_path('.git/packed-refs');
        if (File::exists($packed) && preg_match('/^([0-9a-f]{40}) '.preg_quote($ref, '/').'$/m', (string) File::get($packed), $match)) {
            return $match[1];
        }

        return null;
    }

    /** @return array{ok: bool, pending: list<string>, error: ?string} */
    public function migrations(): array
    {
        try {
            $migrator = app('migrator');
            $repository = $migrator->getRepository();
            if (! $repository->repositoryExists()) {
                return ['ok' => false, 'pending' => [], 'error' => 'ยังไม่มีตาราง migrations'];
            }
            $files = $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));
            $pending = array_values(array_diff(array_keys($files), $repository->getRan()));
            sort($pending);

            return ['ok' => $pending === [], 'pending' => $pending, 'error' => null];
        } catch (Throwable $exception) {
            return ['ok' => false, 'pending' => [], 'error' => $exception->getMessage()];
        }
    }

    public function environment(): array
    {
        $manifest = public_path('build/manifest.json');

        return [
            'app_env' => app()->environment(),
            'app_debug' => (bool) config('app.debug'),
            'app_url' => config('app.url'),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'db_connection' => config('database.default'),
            'db_database' => config('database.connections.'.config('database.default').'.database'),
            'config_cached' => app()->configurationIsCached(),
            'routes_cached' => app()->routesAreCached(),
            'vite_hot' => File::exists(public_path('hot')),
            'build_manifest_at' => File::exists($manifest) ? date('c', File::lastModified($manifest)) : null,
            'maintenance' => app()->isDownForMaintenance(),
        ];
    }

    /** Latest runs of the deploy workflow (needs GITHUB_DEPLOY_TOKEN with Actions: read). Cached 30s. */
    public function githubRuns(): array
    {
        $token = (string) config('services.github.token');
        $repository = (string) config('services.github.repository');
        $workflow = (string) config('services.github.deploy_workflow');
        $actionsUrl = "https://github.com/{$repository}/actions";
        if ($token === '') {
            return ['configured' => false, 'runs' => [], 'error' => null, 'actions_url' => $actionsUrl];
        }

        return Cache::remember('deploy-status.github-runs', 30, function () use ($token, $repository, $workflow, $actionsUrl) {
            try {
                $response = Http::withToken($token)->acceptJson()->timeout(8)
                    ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
                    ->get("https://api.github.com/repos/{$repository}/actions/workflows/{$workflow}/runs", ['per_page' => 10]);
                if (! $response->successful()) {
                    return ['configured' => true, 'runs' => [], 'error' => 'GitHub ตอบกลับ HTTP '.$response->status(), 'actions_url' => $actionsUrl];
                }
                $runs = collect($response->json('workflow_runs', []))->map(fn (array $run) => [
                    'id' => $run['id'],
                    'run_number' => $run['run_number'],
                    'title' => $run['display_title'] ?? '',
                    'status' => $run['status'],
                    'conclusion' => $run['conclusion'],
                    'sha' => $run['head_sha'],
                    'branch' => $run['head_branch'],
                    'actor' => $run['actor']['login'] ?? null,
                    'started_at' => $run['run_started_at'] ?? $run['created_at'],
                    'updated_at' => $run['updated_at'],
                    'url' => $run['html_url'],
                ])->values()->all();

                return ['configured' => true, 'runs' => $runs, 'error' => null, 'actions_url' => $actionsUrl];
            } catch (Throwable $exception) {
                return ['configured' => true, 'runs' => [], 'error' => 'ติดต่อ GitHub ไม่ได้: '.$exception->getMessage(), 'actions_url' => $actionsUrl];
            }
        });
    }

    /** One-line verdict for the top of the page. */
    public function verdict(?array $deployed, array $github, array $migrations): array
    {
        $runs = collect($github['runs']);
        $running = $runs->first(fn ($run) => in_array($run['status'], ['queued', 'in_progress', 'waiting', 'pending'], true));
        $latestSuccess = $runs->first(fn ($run) => $run['conclusion'] === 'success');
        $latest = $runs->first();

        if ($running) {
            return ['level' => 'info', 'text' => "กำลัง deploy run #{$running['run_number']} · {$running['title']}"];
        }
        if (! $migrations['ok'] && $migrations['pending']) {
            return ['level' => 'danger', 'text' => 'มี migration ที่ยังไม่ได้รัน '.count($migrations['pending']).' รายการ · รัน php artisan migrate บนเซิร์ฟเวอร์'];
        }
        if ($latest && $latest['conclusion'] && $latest['conclusion'] !== 'success') {
            return ['level' => 'danger', 'text' => "Deploy ล่าสุด run #{$latest['run_number']} ไม่สำเร็จ ({$latest['conclusion']})"];
        }
        if ($deployed && $latestSuccess && $deployed['sha'] !== $latestSuccess['sha']) {
            return ['level' => 'warning', 'text' => 'เซิร์ฟเวอร์นี้ยังไม่ใช่เวอร์ชันล่าสุดที่ deploy สำเร็จ'];
        }
        if ($deployed && $latestSuccess) {
            return ['level' => 'success', 'text' => 'เซิร์ฟเวอร์นี้เป็นเวอร์ชันล่าสุดแล้ว'];
        }
        if ($deployed) {
            return ['level' => 'success', 'text' => "Deploy run #{$deployed['run_number']} อยู่บนเซิร์ฟเวอร์นี้"];
        }

        return ['level' => 'muted', 'text' => 'เครื่องนี้ไม่ได้ติดตั้งผ่าน GitHub Actions (ไม่มี deploy-info.json)'];
    }
}
