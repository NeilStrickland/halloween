<?php

$frog_dir = $_SERVER['DOCUMENT_ROOT'] . '/include/frog';
require_once($frog_dir . '/frog.inc');

require_once('halloween_class.inc');

$halloween = new halloween_class();
$halloween->name = 'halloween';
$halloween->include_dir = dirname(__FILE__);

require_once('config.inc');

if (! $halloween->data_dir) {
 trigger_error('No halloween data directory',E_USER_ERROR);
 exit;
}

$halloween->log_dir = $halloween->data_dir . '/log';
$halloween->login_log_file = $halloween->log_dir . '/login_log.txt';
$halloween->sessions_dir = $halloween->data_dir . "/sessions";

$cred = trim(file_get_contents($halloween->data_dir . '/cred.txt'));
$halloween->connect('halloween',$cred,'halloween');
$cred = '';

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
mb_http_output("UTF-8");

ini_set('default_charset','utf-8');
ini_set('session.save_path',$halloween->sessions_dir);
ini_set('session.gc_maxlifetime',7200);

if (! session_id()) { session_start(); }

$halloween->register('user');
$halloween->register('track');
