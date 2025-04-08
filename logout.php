<?php
session_start();
session_unset(); // destroying all session vars
session_destroy();
header("Location: login.php");
exit();
