<?php
function createTextbox($arr){
    if(array_key_exists("value", $arr)){
        if($arr["value"] === True){
            if(isset($_GET[$arr["name"]])){
                $arr["value"] = htmlspecialchars($_GET[$arr["name"]]);
            }else{
                unset($arr["value"]);
            }
        }
    }
    $s = '<input type="text" ';
    foreach($arr as $k => $v)
        $s .= "${k}=\"${v}\" ";
    echo $s . ">";
}

function alert($message){
    echo "<script>bootstrap_alert.warning(\"${message}\");</script>";
}

function trace($txt){
    echo "<pre>" . $txt . "</pre>";
}

function printr($var){
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}

function ret($arr){
    foreach($arr as $v){
        if($v != Null) return $v;
    }
}

function startswith($haystack, $needle){
    return !strncasecmp($haystack, $needle, strlen($needle));
}

function ustr_split($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

function ustrlen($str){
    return mb_strlen($str, "UTF-8");
}

function usubstr($str, $s, $t=Null){
    if($t === Null) $t = ustrlen($str);
    return mb_substr($str, $s, $t, "UTF-8");
}

function usubstr_replace($output, $replace, $posOpen, $posClose){
    $tmp = usubstr($output, 0, $posOpen) . 
           $replace . 
           usubstr($output, $posClose + $posOpen);
    return $tmp;
} 

function uchar($s, $i){
    return usubstr($s, $i, 1);
}

function casttoclass($class, $object){
    return unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($class) . 
                                    ':"' . $class . '"', serialize($object)));
}

function itergroup($arr, $lim){
    $save = array();
    $tmp = array();
    foreach($arr as $item){
        $tmp[] = $item;
        if(count($tmp) == $lim){
            $save[] = $tmp;
            $tmp = array();
        }
    }
    $save[] = $tmp;
    return $save;
}

/*
function uord($str, $encoding = 'UTF-8'){
    $str = mb_convert_encoding($str,"UCS-4BE",$encoding);
    $ords = array();
    for($i = 0; $i < mb_strlen($str,"UCS-4BE"); $i++){       
        $s2 = mb_substr($str,$i,1,"UCS-4BE");                   
        $val = unpack("N",$s2);           
        $ords[] = $val[1];               
    }       
    return $ords[0];
}

function uchr($ords, $encoding = 'UTF-8'){
    // Turns an array of ordinal values into a string of unicode characters
    $str = '';
    for($i = 0; $i < sizeof($ords); $i++){
        // Pack this number into a 4-byte string
        // (Or multiple one-byte strings, depending on context.)               
        $v = $ords[$i];
        $str .= pack("N",$v);
    }
    $str = mb_convert_encoding($str,$encoding,"UCS-4BE");
    return($str);           
}
* */

?>
