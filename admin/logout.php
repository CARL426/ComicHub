<?php
// admin/logout.php - Logs out and returns to admin login
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit;
