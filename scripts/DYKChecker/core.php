<?php
$op = "~~~OPENSESAME~~~";
$ed = "~~~CLOSESESAME~~~";
$defaultsub = $op . '$1' . $ed;

include 'lib/wp/lib.php';

function calclen($text){
    global $op, $ed;
    $text = str_replace("(", "[", $text);
    $text = str_replace(")", "]", $text);
    $text = str_replace($op, "(", $text);
    $text = str_replace($ed, ")", $text);
    $lv = 0;
    $ans = 0;
    foreach(ustr_split($text) as $i){
        if($i == "(") $lv++;
        else if($i == ")") $lv--;
        else if($lv == 0 and $i != " " and $i != "\n") $ans++;
    }
    return $ans;
}

function grading($r){
    return $r ? "passed" : "failed";
}

function rempair($begin, $end, $text){
    global $op, $ed;
    return str_replace($begin, $op . $begin, 
           str_replace($end, $end . $ed, $text));
}

function remdef($p, $txt){
    global $defaultsub;
    return preg_replace($p, $defaultsub, $txt);
}

function rem($text){
    global $op, $ed;
    
    $text = preg_replace("@[ \t]+@", " ", $text);
    
    $text = rempair("<!--", "-->", $text);
    $text = rempair("{|", "|}", $text);
    $text = rempair("{{", "}}", $text);
    $text = rempair("<gallery>", "</gallery>", $text);
    $text = rempair("<div", "</div>", $text);
    $text = rempair("<math>", "</math>", $text);
    
    $text = preg_replace('@(\[\[[^\]\|\[]*\|)(.*?)(\]\])@', "$op$1$ed$2$op$3$ed", $text);
    
    $text = remdef("@(< ?/? ?(br|center|sup|sub) ?/? ?>)@", $text);
    $text = remdef("@(<br ?/? ?>)@", $text);
    $text = remdef('@(<ref[^>]*?/ ?>)@s', $text);
    $text = remdef('@(<ref[^>/]*?>.*?</ref>)@s', $text);
    $text = remdef('@(?<!\[)(\[(?!\[) *http://.*?\])@s', $text);
    $text = remdef('@(http://\S*)@', $text);
    $text = remdef('@(\[\[[^\]\|]*?\:.*?\]\])@s', $text);
    $text = remdef('@(\'{2,})@', $text);
    $text = remdef('@^(== ?(อ้างอิง|ดูเพิ่ม|แหล่งข้อมูลอื่น|เชิงอรรถ) ?== ?$.*)$@ms', $text);
    $text = remdef('@(^=+ ?|=+ ?$)@m', $text);
    $text = remdef('@^([\:\*\#]+)@m', $text);
    //$text = preg_replace('@(<(.*?)>.*?</(\2)>)@ms', $defaultsub, $text);
    
    $text = rempair("[", "[", $text);
    $text = rempair("]", "]", $text);
    return $text;
}

function finalize($arr){
    global $op, $ed;
    $arr["newtext"] = str_replace("\n", "<br/>",
                      str_replace($op, '<span class="eqtext">',
                      str_replace($ed, '</span>',
                      htmlspecialchars($arr["newtext"]))));
    return $arr;
}

function connectwp($title){
    $now = Timestamp::getcurrenttime();
    $site = new Site("th");
    $page = new Page($site, $title);
    try{
        $text = $page->get();
    }catch(Exception $e){
        return array("error" => "ไม่มีหน้าดังกล่าว");
    }
    try{
        $refcnt = substr_count($text, '<ref');
        $inlinedic = array("value" => "มีอ้างอิงในบรรทัดทั้งหมดจำนวน $refcnt แห่ง",
                           "result" => grading($refcnt > 0),
                           "text" => "อ้างอิง",
                           "desc" => "ข้อความที่ใช้เสนอบทความรู้ไหมว่า" . 
                                     "ต้องมีอ้างอิงในบรรทัดยืนยันอย่างน้อย 1 แหล่ง");
        $oldtext = $text;
        $text = rem($text);
        $lentext = calclen($text);
        $lendic = array("text" => "ความยาว",
                        "result" => grading($lentext >= 2000),
                        "value" => "$lentext อักขระ",
                        "desc" => "บทความรู้ไหมว่าต้องมีความยาวของความเรียงอย่างต่ำ 2000 อักขระ");
        $revts = NULL;
        $lastrevd = NULL;
        $firstround = True;
        foreach($page->gethist() as $i){
            if($firstround){
                $firstround = False;
                $lastrevd = $i;
            }
            $revid = $i["revid"];
            $ts = $i["timestamp"];
            $diff = $ts->diff($now)->days;
            if($diff <= 14){
                $revdiff = $diff;
                $revts = $ts;
            }
            else break;
        }
        $lastrevdic = array("value" => "รุ่นที่ตรวจสอบ คือรุ่นในวันที่ " .
                                       "${lastrevd['timestamp']} (UTC) แก้ไขโดย " .
                                       "${lastrevd['user']}",
                            "result" => "normal",
                            "text" => "ข้อมูลรุ่น",
                            "desc" => "");
        $oldlendic = array("text" => "รุ่นเก่า", 
                           "desc" => "ความยาวความเรียงต้องเพิ่มขึ้นอย่างน้อย 2 เท่า" .
                                     "ในเวลา 14 วัน ยกเว้นเป็นบทความที่เพิ่งสร้างภายใน 14 วัน");
        if($revts == NULL){
            $oldlendic["result"] = grading(False);
            $oldlendic["value"] = "ไม่พบรุ่นเก่าภายในเวลา 14 วัน";
        }else{
            $revobj = new Revision($site, $revid);
            $oldlen = calclen(rem($revobj->get()));
            $oldlendic["result"] = grading(floatval($lentext)/floatval($oldlen) >= 3.0);
            $oldlendic["value"] = "รุ่นเก่าก่อนการแก้ไขเมื่อ " . 
                                  $revts .
                                  " UTC (${revdiff} วันที่แล้ว) มีความยาว ${oldlen} อักขระ " . 
                                  "ขณะนี้มีเนื้อหาเพิ่มขึ้น " . 
                                  (string)((floatval($lentext)/floatval($oldlen))-1.0) . 
                                  " เท่าเมื่อเทียบกับขณะนั้น";
        }
        $firstcontrib = $page->gethist(1, True);
        $firstcontrib = $firstcontrib[0];
        $ts = $firstcontrib["timestamp"];
        $diff = $ts->diff($now)->days;
        $user = $firstcontrib["user"];
        $createdic = array("text" => "สร้างบทความ",
                           "result" => grading($diff <= 14),
                           "value" => "บทความนี้สร้างโดย ${user} เมื่อ ${ts} UTC " . 
                                      "(${diff} วันที่แล้ว)", 
                           "desc" => "ต้องสร้างภายใน 14 วัน " .
                                     "ยกเว้นมีความยาวเพิ่มขึ้น 2 เท่าภายใน 14 วัน");
        if($createdic["result"] == grading(True) or 
           $oldlendic["result"] == grading(True)){
            if($createdic["result"] == grading(False))
                $createdic["result"] = "normal";
            if($oldlendic["result"] == grading(False))
                $oldlendic["result"] = "normal";
        }
        return finalize(array(
            "lastrev" => $lastrevdic,
            "inline" => $inlinedic,
            "newtext" => $text,
            "len" => $lendic,
            "oldlen" => $oldlendic,
            "create" => $createdic
        ));
            
    }catch(Exception $e){
        echo "<!--" . $e . "-->";
        return array("error" => "พบข้อผิดพลาดไม่ทราบสาเหตุ (รายละเอียดเพิ่มเติมดูที่ซอร์ซของไฟล์)");
    }
}
?>
