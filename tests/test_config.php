<?php
$config['site_url'] = getenv('comics_site');
$config['secret_key'] = getenv('comics_key');

$config['db']['db_user'] = "php_test";
$config['db']['db_password'] = "password";
$config['db']['db_name'] = 'comics_test';
$config['db']['db_type'] = 'sqlite';
$config['db']['db_file'] = sys_get_temp_dir() . '/comics_test.db';

return $config;
