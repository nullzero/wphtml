<?php
global $table;
$table = array();
$table["th"] = array(
    "Page generated in" => "หน้านี้สร้างขึ้นภายใน",
    "seconds" => "วินาที",
    "Nullzero's tools" => "เครื่องมือของ Nullzero",
    "Home" => "หน้าหลัก",
    "Contact" => "ติดต่อ",
    "Tools" => "เครื่องมือ",
    "Bot's status" => "สถานะของบอต",
    "All tools" => "เครื่องมือทั้งหมด",
    "If you encountered an error, please contact" => "พบข้อผิดพลาด โปรดติดต่อ",
    "tools' owner" => "ผู้พัฒนาเครื่องมือ",
);

function _L10N($arr, $msg){
    global $table;
    if(isset($arr["uselang"])){
        if(array_key_exists($arr["uselang"], $table)){
            if(array_key_exists($msg, $table[$arr["uselang"]])){
                return $table[$arr["uselang"]][$msg];
            }else{
                return "<span style='color: green'>${msg}</span>";
            }
        }
        return $msg;
    }
    return Null;
}

function L10N($msg){
    return ret(array(_L10N($GLOBALS, $msg), _L10N($_COOKIE, $msg), $msg));
}
?>
