<?php
    function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = utf8ize($v);
            }
        } else if (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
    }

    include "connection.php";
    include "rc6.php";

    $id = $_POST['id'];
    
    $sql = $pdo->prepare("SELECT * FROM user WHERE id=:a");
	$sql->bindParam(':a', $id);
    $sql->execute();
    $data = $sql->fetchAll()[0];

    $out = array();
    $out['pwd-dec'] = trim(decrypt($data["password"], $data["keygen"]));
    $out['key'] = $data["keygen"];
    $out['pwd-enc'] = $data["password"];
    $out['pwd-enc2'] = bin2hex($data["password"]);
    $out['sbox'] = getSBox($data["keygen"]);

    echo json_encode(utf8ize( $out ));
?>