<?php

require_once "includes/bootstrap.php";

$_SESSION = [];

session_regenerate_id(true);
session_destroy();

header("Location: home.php");
exit;