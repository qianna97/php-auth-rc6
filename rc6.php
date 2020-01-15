<?php
function getStr($string, $start, $end){
    $r = "";
    for($i=$start; $i<$end; $i++){
        $r .= $string[$i];
    }
    return $r;
}

function fromBlock($blocks){
    $s = "";
    for($i=0; $i<count($blocks); $i++){
        $temp = decbin($blocks[$i]);
        $v = "";
        if(strlen($temp) < 32){
            for($p=0; $p<32-strlen($temp); $p++){
                $v .= "0";
            }
            $temp = $v.$temp;
        }

        for($p=0; $p<4; $p++){
            $s .= chr(intval(getStr($temp, $p*8, ($p+1)*8), 2));
        }
    }
    return $s;
}

function toBlock($sentence){
    $encoded = [];
    $res = "";
    for($i=0; $i<strlen($sentence); $i++){
        if($i % 4 == 0 && $i != 0){
            array_push($encoded, $res);
            $res = "";
        }
        $temp = decbin(ord($sentence[$i]));
        $v = "";
        if(strlen($temp)<8){
            for($p=0; $p<8-strlen($temp); $p++){
               $v .= "0";
            }
            $temp = $v . $temp;
        }
        $res .= $temp;
    }
    array_push($encoded, $res);
    return $encoded;
}

function RotateRight($x, $n, $bits = 32){
    $mask = (2**$n) - 1;
    $mask_bits = $x & $mask;
    return ($x >> $n) | ($mask_bits << ($bits - $n));
}

function RotateLeft($x, $n, $bits = 32){
    $mask = (2**($bits - $n)) - 1;
    $mask_bits = $x & $mask;
    return ($x >> ($bits - $n)) | ($mask_bits << ($bits - ($bits - $n)));
}

function KeyGenerator($userkey){
    $ul = strlen($userkey);

    if($ul < 16){
        for($i=0; $i<16-$ul; $i++){
            $userkey .= " ";
        }
    }

    $r = 12;
    $w = 32;
    $b = strlen($userkey);
    $modulo = 2**32;
    $s = [];
    
    for($i=0; $i<(2*$r+4); $i++){
        array_push($s, "0");
    }

    $s[0]=0xB7E15163;

    for($i=1; $i<2*$r+4; $i++){
        $s[$i] = $s[$i-1]+0x9E3779B9;
    }

    $encoded = toBlock($userkey);
    $enlength = count($encoded);
    
    $l = [];
    for($i=0; $i<$enlength; $i++){
        array_push($l, "0");
    }

    for($i=1; $i<$enlength+1; $i++){
        $l[$enlength-$i] = intval($encoded[$i-1], 2);
    }

    $v = 3*max($enlength, 2*$r+4);
    
    $A = 0;
    $B = 0;
    $i = 0;
    $j = 0;

    for($p=0; $p<$v; $p++){
        $A = $s[$i] = RotateLeft(($s[$i] + $A + $B), 3);

        $B = $l[$j] = RotateLeft(($l[$j] + $A + $B), ($A+$B) % 32);

        $i = fmod(($i + 1), (2*$r + 4));
        $j = fmod(($j + 1), $enlength);
    }

    return $s;
}

function encrypt($sentence, $key){
    $s = KeyGenerator($key);

    $ul = strlen($sentence);
    if($ul < 16){
        for($i=0; $i<16-$ul; $i++){
            $sentence .= " ";
        }
    }

    $encoded = toBlock($sentence);
    $enlength = count($encoded);

    $A = intval($encoded[0], 2);
    $B = intval($encoded[1], 2);
    $C = intval($encoded[2], 2);
    $D = intval($encoded[3], 2);

    $r = 12;
    $w = 32;
    $modulo = 2**32;
    $lgw = 5;

    $B = gmp_mod(($B + $s[0]), $modulo);
    $D = gmp_mod(($D + $s[1]), $modulo);

    for($i=1; $i<$r+1; $i++){
        $t_temp = gmp_mod(($B*(2*$B + 1)), $modulo);
        $t = RotateLeft($t_temp, $lgw, 32);
        
        $u_temp = gmp_mod(($D*(2*$D + 1)), $modulo);
        $u = RotateLeft($u_temp, $lgw, 32);
        
        $tmod = gmp_mod($t, 32);
        
        $umod = gmp_mod($u, 32);

        $A = gmp_mod((RotateLeft($A^$t, $umod, 32) + $s[2*$i]), $modulo); 
        $C = gmp_mod((RotateLeft($C^$u, $tmod, 32) + $s[2*$i+ 1]), $modulo);
        
        $temp_ = $A;
        $A = $B; 
        $B = $C;
        $C = $D;
        $D = $temp_;
    }
    $A = gmp_mod(($A + $s[2*$r + 2]), $modulo);
    $C = gmp_mod(($C + $s[2*$r + 3]), $modulo);

    $cipher = [];
    
    array_push($cipher, $A);
    array_push($cipher, $B);
    array_push($cipher, $C);
    array_push($cipher, $D);

    return fromBlock($cipher);
}

function decrypt($esentence, $key){
    $s = KeyGenerator($key);

    $encoded = toBlock($esentence);
    $enlength = count($encoded);

    $A = intval($encoded[0],2);
    $B = intval($encoded[1],2);
    $C = intval($encoded[2],2);
    $D = intval($encoded[3],2);
    
    $r = 12;
    $w = 32;
    $modulo = 2**32;
    $lgw = 5;
    
    $C = gmp_mod(($C - $s[2*$r+3]), $modulo);
    $A = gmp_mod(($A - $s[2*$r+2]), $modulo);
    
    for($j=1; $j<$r+1; $j++){
        $i = $r+1-$j;
        
        $temp_A = $A;
        $temp_B = $B;
        $temp_C = $C;
        $temp_D = $D;

        $A = $temp_D;
        $B = $temp_A;
        $C = $temp_B;
        $D = $temp_C;

        $u_temp = gmp_mod(($D*(2*$D + 1)), $modulo);
        $u = RotateLeft($u_temp, $lgw, 32);
        
        $t_temp = gmp_mod(($B*(2*$B + 1)), $modulo);
        $t = RotateLeft($t_temp, $lgw, 32);
        
        $tmod = gmp_mod($t, 32);
        $umod = gmp_mod($u, 32);

        $C = (RotateRight(gmp_mod(($C-$s[2*$i+1]), $modulo), $tmod, 32)  ^$u);  
        $A = (RotateRight(gmp_mod(($A-$s[2*$i]), $modulo), $umod, 32)   ^$t);
    
    }

    $D = gmp_mod(($D - $s[1]), $modulo);
    $B = gmp_mod(($B - $s[0]), $modulo);

    $orgi = [];
    array_push($orgi, $A);
    array_push($orgi, $B);
    array_push($orgi, $C);
    array_push($orgi, $D);
    
    return fromBlock($orgi);
}

function RandKey(){
    $key = "";

    while(strlen($key) < 10){
        $p = rand(33, 126);
        $c = chr($p);
        $key .= $c;
    }

    return $key;
}

function getSBox($userkey){
    $ul = strlen($userkey);

    if($ul < 16){
        for($i=0; $i<16-$ul; $i++){
            $userkey .= " ";
        }
    }

    $r = 12;
    $w = 32;
    $modulo = 2**32;
    $s = [];
    
    for($i=0; $i<(2*$r+4); $i++){
        array_push($s, "0");
    }

    $s[0]=0xB7E15163;

    for($i=1; $i<2*$r+4; $i++){
        $s[$i]=fmod(($s[$i-1]+0x9E3779B9), (2**$w));
    }

    $encoded = toBlock($userkey);
    $enlength = count($encoded);
    
    $l = [];
    for($i=0; $i<$enlength; $i++){
        array_push($l, "0");
    }

    for($i=1; $i<$enlength+1; $i++){
        $l[$enlength-$i] = intval($encoded[$i-1], 2);
    }

    $v = 3*max($enlength, 2*$r+4);
    
    $A = 0;
    $B = 0;
    $i = 0;
    $j = 0;

    for($p=0; $p<$v; $p++){
        $A = $s[$i] = RotateLeft(($s[$i] + $A + $B) % $modulo, 3, 32);

        $B = $l[$j] = RotateLeft(($l[$j] + $A + $B) % $modulo, ($A+$B) % 32, 32);

        $i = fmod(($i + 1), (2*$r + 4));
        $j = fmod(($j + 1), $enlength);
    }
    return implode("-",$s);
}
?>