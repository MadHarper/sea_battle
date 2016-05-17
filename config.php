<?php
define('DBLOCATION', 'localhost');
define('DBNAME', 'seabattle');
define('DBUSER', 'madharper');
define('DBPASS', 1);
define('DBPORT', '3306');

$db = mysqli_connect(DBLOCATION, DBUSER, DBPASS, DBNAME, DBPORT) or die(mysqli_connect_error());;