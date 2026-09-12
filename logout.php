<?php
session_start();

after:
$_SESSION = [];
session_destroy();
require_once __DIR__ . '/includes/functions.php';
redirect('login.php');
exit;
