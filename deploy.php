<?php
namespace Deployer;

require 'recipe/common.php';

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


set('shared_files', [
    'qa-config.php',
]);


task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    // 'deploy:vendors',
    'deploy:shared',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
])->desc('Deploy your project');
after('deploy', 'success');
