<?php
namespace Environment;

require 'core/consts.php';

session_name(SESSION_INITIAL_NAME);
session_start();
session_unset();
session_destroy();

header('Location: ' . SYSTEM_HOST);
?>