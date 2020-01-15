<?php
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'rc6_db';

    $pdo = new PDO('mysql:host='.$host.';dbname='.$database, $username, $password);
?>