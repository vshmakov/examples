<?php

namespace Deployer;

require 'recipe/common.php';

inventory('config/prod/hosts.yaml');

set('composer_options', '{{composer_action}} --no-scripts');

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
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:test',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success',
]);

after('deploy:failed', 'deploy:unlock');

task('deploy:test', function (): void {
    run('{{symfony/console}} doctrine:migrations:migrate -n -e test');
    run('{{symfony/console}} doctrine:schema:validate -e test');
    run('{{symfony/console}} doctrine:fixtures:load -n -e test');
    run('{{bin/php}} {{release_path}}/bin/phpunit');
});

task('pwd', function () {
    $result = run('{{bin/php}}   ~/www/test.exmasters/releases/1/vendor/symfony/var-dumper/Resources/functions/dump.php');
    writeln("$result");
});
