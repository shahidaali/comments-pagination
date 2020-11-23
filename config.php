<?php
define('DB_HOST', "localhost");
define('DB_USER', "root");
define('DB_PASSWORD', "");
define('DB_NAME', "php_comments");

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli ->connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli ->connect_error;
  exit();
}