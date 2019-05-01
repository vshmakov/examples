<?php

namespace Deployer;

require 'recipe/common.php';

inventory('config/prod/hosts.yaml');

set('composer_options', '{{composer_action}} --no-scripts');
set('ssh_multiplexing', false);
set('symfony/console', '{{bin/php}} {{release_path}}/bin/console');

const  LOGS_DIR = 'var/logs';
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
    'COMPOSER_MEMORY_LIMIT' => -1,
]);

desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    //'deploy:upload-assets',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:test',
    'deploy:clear_paths',
    'deploy:build',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success',
]);

after('deploy:failed', 'deploy:unlock');
set('release/composer', 'cd {{release_path}} && {{bin/composer}}');
task('deploy:build', function (): void {
    run('{{release/composer}} dump-env prod');
    run('{{release/composer}} dump-autoload --optimize --no-dev --classmap-authoritative');
});

task('deploy:upload-assets', function (): void {
    upload('/C/OSPanel/domains/examples/composer.json', '~/abc.def');
});

task('deploy:test', function (): void {
    run('{{symfony/console}} doctrine:migrations:migrate -n -e test');
    run('{{symfony/console}} doctrine:schema:validate -e test');
    run('{{symfony/console}} doctrine:fixtures:load -n -e test');
    run('cd {{release_path}} && {{bin/php}} vendor/bin/phpunit');
});

after('deploy:vendors', 'deploy:post-install');
task('deploy:post-install', function (): void {
    run('cd {{release_path}} && {{bin/composer}} run-script auto-scripts');
});
