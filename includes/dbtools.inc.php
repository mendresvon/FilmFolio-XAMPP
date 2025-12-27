<?php
// database connection settings
$db_server = 'localhost';
$db_username = 'root'; 
$db_password = '';      
$db_name = 'filmfolio_db';

// establish connection to mysql
$link = mysqli_connect($db_server, $db_username, $db_password, $db_name);

if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// set charset to utf8 for proper text encoding
mysqli_set_charset($link, "utf8");
?>