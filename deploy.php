<?php
namespace Deployer;

require 'recipe/symfony4.php';

// Project name
set('application', 'BeWelcome');

// Project repository
set('repository', 'git@github.com:BeWelcome/rox.git');

set('deploy_path', '~/{{application}}');
set('default_stage', 'staging');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// Shared files/dirs between deploys
add('shared_files', ['.env', '.env.local']);
add('shared_dirs', ['var/logs', 'var/sessions']);

// Writable dirs by web server
add('writable_dirs', ['data']);

desc('Create local .env file');
task('deployer:create-env', function () {
    $localEnvPath = "{{release_path}}/.env.local";
    if (!test("[ -s $localEnvPath ]")) {
        run("echo 'APP_ENV=prod' >> $localEnvPath");
        run("echo 'DB_HOST={{db_host}}' >> $localEnvPath");
        run("echo 'DB_PORT={{db_port}}' >> $localEnvPath");
        run("echo 'DB_NAME={{db_name}}' >> $localEnvPath");
        run("echo 'DB_USER={{db_user}}' >> $localEnvPath");
        run("echo 'DB_PASS={{db_pass}}' >> $localEnvPath");
        writeln('Created file .env.local');
    } else {
        writeln('File .env.local already exists');
    }
});
before('deploy:vendors', 'deployer:create-env');

desc('Dump .env files for production');
task('deploy:dump-env', function () {
    $result = run('cd {{release_path}} && {{bin/composer}} dump-env prod');
    writeln($result);
});
after('deploy:vendors', 'deploy:dump-env');

// Tasks

task('build', function () {
    run('cd {{release_path}} && make build version');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// config.deployer.yml is git-ignored
inventory('config/deployer.yml');
