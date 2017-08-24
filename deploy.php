<?php
namespace Deployer;

require 'recipe/laravel.php';

// Configuration

set('repository', 'git@github.com:cmcfadden/question2answer.git');
set('git_tty', true); // [Optional] Allocate tty for git on first deployment
add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);


// Hosts

host('cla-q2a-dev')
    ->stage('beta')
    ->set('deploy_path', '/swadm/var/www/html/');
 

// Tasks


// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

