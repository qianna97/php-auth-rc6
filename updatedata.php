<?php
    include "connection.php";
    include "rc6.php";

    $id = $_POST['id'];
    $keygen = $_POST['keygen'];
    $password = $_POST['password'];

    $query = $pdo->prepare("UPDATE user SET keygen = :keygen, password = :password WHERE id = :id");

    $query->bindParam(':keygen', $keygen);
    $query->bindParam(':password', encrypt($password, $keygen));
    $query->bindParam(':id', $id);

    $query->execute();

    echo json_encode(1);
?>