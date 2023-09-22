<?php

$cfg['path'] = [
    'app_root' => '',
    'tmp' => '/tmp',
    'data' => '/data',
    'caroot' => '/data/caroot.pem',
];

$cfg['main'] = [
    'db' => '', //Mysqli or SQLite (not tested)
    'timezone' => 'PRC',
    'dbprefix' => '',
    'encoding' => 'utf-8',
];

$cfg['debug'] = [
    'enable' => 0,
    'log_error' => 0,
];

$cfg['mysql'] = [
    'host' => 'localhost',
    'username' => '',
    'password' => '',
    'dbname' => '',
    'encoding' => 'utf8mb4',
    'explain_level' => -1,
    'explain_log' => null,
];

$cfg['sqlite'] = [
    //    'path' => '/web.db'
];
