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
    
    include "rc6.php";

    $pass = $_POST['pass'];
    $keyy = $_POST['keyy'];

    $out = array();
    $out['pwd-enc'] = encrypt($pass, $keyy);
    $out['pwd-enc2'] = bin2hex(encrypt($pass, $keyy));
    $out['sbox'] = getSBox($keyy);

    echo json_encode(utf8ize( $out ));

?>