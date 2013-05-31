<?php
include 'lib/wp/lib.php';

$prepat = "(?:\[\[|\{\{)";
$pat = "~~~#!AmarkerZ%\d+%ZmarkerA!#~~~";
$patINS = "~~~#!AahrefZ%\d+%ZahrefA!#~~~";
$patEINS = "~~~#!AendaZ%\d+%ZendaA!#~~~";

function rmtag($tag, $text){
    return preg_replace("@<${tag}>.*?</${tag}>@s", "", $text);
}

function _translate($arr, $site, $siteDest){
    $medium = $site->langlinks1site(array_values($arr), $siteDest);
    $narr = array();
    foreach($arr as $k => $v){
        $v = ucfirst($v);
        if(array_key_exists($v, $medium)){
            $narr[$k] = array();
            $val = $medium[$v];
            if(strtoupper($val) != $val){
                // A heuristic to lcfirst
                if(!strstr($val, ":")) $val = ":" . $val;
                $val = preg_replace_callback('@^(.*?):(.*?)$@', function($matches){
                    return $matches[1] . ":" . lcfirst($matches[2]);
                }, $val);
                if(startswith($val, ":")) $val = substr($val, 1);
            }
            $narr[$k]["translated"] = $val;
            $narr[$k]["old"] = $arr[$k];
        }
    }
    return $narr;
}

function translate($arr, $site, $siteDest){
    global $pat, $patINS, $patEINS;
    foreach($arr as $i => $val) $arr[$i] = preg_replace("@${pat}@", "", $val);
    $transArray = array();
    $keyArray = array();
    foreach($arr as $j => $i){
        if(startswith($i, "[[")){
            $i = preg_replace("@^\[+@", "", $i);
            $i = preg_replace("@#.*$@", "", $i);
            if(startswith($i, "Category:") or !startswith($i, ":")){
                $transArray[$j] = $i;
            }
        }else if(startswith($i, "{{")){
            $i = substr($i, 2);
            if(!strstr($i, ":")){
                $transArray[$j] = "Template:" . $i;
            }else{
                $transArray[$j] = $i;
            }
        }
        $keyArray[] = $i;
    }
    $transArray = _translate($transArray, $site, $siteDest);
    foreach($arr as $i => $val){
        if(array_key_exists($i, $transArray)){
            $array = $transArray[$i];
            $arr[$i] = str_replace($keyArray[$i], 
                                   str_replace("\d+", $array['old'], $patINS) . 
                                   $array['translated'] .
                                   $patEINS, $val);
            //$arr[$i] = str_replace($keyArray[$i], $array['translated'], $val);
        }
    }
    return $arr;
}

function clean($text){
    global $pat, $patINS;
    $patr = str_replace("\d+", ".*?", $patINS);
    
    return preg_replace("@\{\{(${patr})?แม่แบบ:@", '{{$1', 
           preg_replace("@\{\{(${patr})?Template:@i", '{{$1',
           preg_replace("@\[\[(${patr})?Category:@i", '[[$1หมวดหมู่:',
           preg_replace("@\[\[(${patr})?Image:@i", '[[$1ไฟล์:',
           preg_replace("@\[\[(${patr})?File:@i", '[[$1ไฟล์:',
           preg_replace('@^== *See also *== *$@im', "== ดูเพิ่ม ==", 
           preg_replace('@^== *External links *== *$@im', "== แหล่งข้อมูลอื่น ==", 
           preg_replace('@^== *References *== *$@im', "== อ้างอิง ==",
           str_replace("\r", "", // second order
           preg_replace("@${pat}@", "", $text)))))))))); // first order
}

function finalize($text, $site){
    global $patINS, $patEINS;
    $text = clean($text);
    $text = htmlspecialchars($text);
    $patr = str_replace("\d+", "(.*?)", $patINS);
    $text = preg_replace("@${patr}@s", "<a href='" . $site->link() . "\$1' " . 
                                "title='$1'>", $text);
    $text = str_replace($patEINS, "</a>", $text);
    return $text;
}

function connectwp($title, $site, $siteDest, $content){
    $site = new Site($site);
    $page = new Page($site, $title);
    if(empty($title)) $page->txt = $content;
    try{
        $text = $page->get();
    }catch(Exception $e){
        return array("error" => "ไม่มีหน้าดังกล่าว");
    }
    global $cnt;
    $cnt = 0;
    foreach(array("{{", "[[") as $tag){
        $text = preg_replace_callback("@(" . preg_quote($tag) . ")@s",
                                      function($matches){
                    global $cnt, $pat;
                    $cnt++;
                    return str_replace("\d+", $cnt, $pat) . $matches[0];
                }, $text);
        $cnt++;
    }
    // nowiki
    $rmtext = $text;
    $rmtext = rmtag("pre", $rmtext);
    $rmtext = rmtag("nowiki", $rmtext);
    $rmtext = rmtag("source", $rmtext);
    $rmtext = preg_replace("@<!--.*?-->@s", "", $rmtext);
    $matches = Null;
    global $prepat, $pat;
    preg_match_all("@(${pat})(${prepat}.*?)(?=[|}\]])@", $rmtext, $matches);
    $transArray = translate($matches[2], $site, $siteDest);
    $text = str_replace($matches[0], $transArray, $text);
    return array("text" => finalize($text, $site));
}
?>
