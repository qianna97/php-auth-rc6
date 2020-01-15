<?php
    include "connection.php";
    include "rc6.php";

    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm-password'];
    $email = $_POST['email'];

    $sql = $pdo->prepare("SELECT * FROM user WHERE username=:a");
    $sql->bindParam(':a', $username);
    $sql->execute();
    $data = $sql->fetch();

    $err = false;
    
    if(!empty($data)){
        $message = "Username telah digunakan";
        $err = true;
    }else{
        if(strlen($username) < 4){
            $message = "Username kurang";
            $err = true;
        }
    }
    
    if($password != $confirm){
        $message = "Password tidak cocok";
        $err = true;
    }else{
        if(strlen($password) < 8){
            $message = "Password kurang";
            $err = true;
        }
    }

    if($err == true){
        echo "<script type='text/javascript'>alert('$message');
        window.location.href='index.php';
        </script>";
    }else{
        $keygen = RandKey();

        $pw = encrypt($password, $keygen);

        $sql = $pdo->prepare("INSERT INTO user (username, password, email, keygen) VALUES (:u, :p, :e, :k)");
        $sql->bindParam(':u', $username);
        $sql->bindParam(':p', $pw);
        $sql->bindParam(':e', $email);
        $sql->bindParam(':k', $keygen);
        $sql->execute();
        $data = $sql->fetch();

        $message = "Registrasi Sukses, silahkan login";
        echo "<script type='text/javascript'>alert('$message');
        window.location.href='index.php';
        </script>";
    }

?>