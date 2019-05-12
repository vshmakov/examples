<?php

namespace Deployer;

use App\Deploy\AsyncProcess;
use  Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

require 'recipe/common.php';

function cdToReleasePath(): void
{
    cd('{{release_path}}');
}

function toLinuxPath(string $path): string
{
    return str_replace('\\', '/', $path);
}

inventory('config/prod/hosts.yaml');

set('composer_options', '{{composer_action}} --no-scripts');
set('ssh_multiplexing', false);
set('symfony/console', '{{bin/php}} {{release_path}}/bin/console');

const  LOGS_DIR = 'var/log';
const  CACHE_DIR = 'var/cache';

set('shared_dirs', [LOGS_DIR]);
set('shared_files', [
    '.env',
    '.env.local',
    '.env.test',
    '.env.test.local',
]);
set('clear_paths', ['.env.local.php']);

set('env', [
    //'COMPOSER_MEMORY_LIMIT' => -1,
]);
set('build_assets_path', 'public/build');

after('deploy:failed', 'deploy:unlock');

task('deploy:create-manifest', function (): void {
    cdToReleasePath();
    run('mkdir {{build_assets_path}} -p');
    run('echo "{}" > {{build_assets_path}}/manifest.json');
});

task('deploy:test', function (): void {
    cdToReleasePath();
    run('{{symfony/console}} doctrine:migrations:migrate -n -e test');
    run('{{symfony/console}} doctrine:schema:validate -e test');
    run('{{symfony/console}} doctrine:fixtures:load -n -e test');
    run('{{bin/php}} vendor/bin/phpunit');
});

task('deploy:migrate', function (): void {
    cdToReleasePath();
    run('{{symfony/console}} doctrine:migrations:migrate -n');
    run('{{symfony/console}} doctrine:schema:validate');
});

after('deploy:vendors', 'deploy:post-install');
task('deploy:post-install', function (): void {
    cd('{{release_path}}');
    run('{{bin/composer}} run-script auto-scripts');
});

task('deploy:build', function (): void {
    cd('{{release_path}}');
    run('{{bin/composer}} dump-env prod');
    run('{{bin/composer}} dump-autoload --optimize --no-dev --classmap-authoritative');
});

/** @var AsyncProcess $buildAssetsCommand */
$buildAssetsCommand = null;

task('deploy:build-assets', function () use (&$buildAssetsCommand): void {
    $buildAssetsCommand = new AsyncProcess('npm run build');
});

task('deploy:upload-assets', function () use (&$buildAssetsCommand): void {
    $buildAssetsCommand->wait();

    $finder = Finder::create()
        ->files()
        ->in(sprintf('%s/%s', __DIR__, get('build_assets_path')));

    /** @var SplFileInfo $file */
    foreach ($finder as $file) {
        set('asset_directory_path', toLinuxPath($file->getRelativePath()));
        set('asset_path', toLinuxPath($file->getRelativePathname()));
        run('mkdir {{release_path}}/{{build_assets_path}}/{{asset_directory_path}} -p');
        runLocally('scp -P {{port}} {{build_assets_path}}/{{asset_path}} {{user}}@{{hostname}}:{{release_path}}/{{build_assets_path}}/{{asset_path}}');
    }
});

desc('Deploy your project');
task('deploy', function (): void {
    after('deploy:info', 'deploy:build-assets');
    after('deploy:create-manifest', 'deploy:test');
    before('deploy:symlink', 'deploy:upload-assets');
    invoke('quik-deploy');
});

task('quik-deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:create-manifest',
    'deploy:clear_paths',
    'deploy:migrate',
    'deploy:build',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success',
]);
