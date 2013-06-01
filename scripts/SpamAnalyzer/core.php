<?php
include 'lib/wp/lib.php';

function connectwp($url){
    $optout = array("BotKung" => True);
    $site = new Site("th");
    $data = array();
    $users = array();
    foreach($site->exturlusage($url, 12) as $item){
        $page = new Page($site, $item["title"]);
        $break = False;
        $adder = Null;
        $limit = 500;
        $revisions = $page->gethist($limit, False);
        $lef = 0;
        $rig = count($revisions) - 1;
        while($lef <= $rig){
            $mid = floor(($lef + $rig) / 2);
            $revisions[$mid]["obj"] = new Revision($site, $revisions[$mid]["revid"]);
            if(strstr($revisions[$mid]["obj"]->get(), $item["url"])){
                $adder = $mid;
                $lef = $mid + 1;
            }else{
                $rig = $mid - 1;
            }
        }
        if(($adder === Null) or ($adder == count($revisions) - 1)){
            $data[] = array("error" => "", 
                            "title" => $item["title"],
                            "url" => $item["url"]);
        }else{
            if(array_key_exists($revisions[$adder]["user"], $optout)){
                continue;
            }
            $cnt = 0;
            $i = $adder + 1;
            while($i < count($revisions)){
                if($revisions[$adder]["user"] == $revisions[$i]["user"]) $cnt++;
                $i++;
            }
            $data[] = array("user" => $revisions[$adder]["user"],
                            "timestamp" => $revisions[$adder]["timestamp"],
                            "url" => $item["url"],
                            "edits" => $cnt,
                            "title" => $item["title"],
                            "text1" => $revisions[$adder+1]["obj"]->get(),
                            "text2" => $revisions[$adder]["obj"]->get(),);
            $users[] = $revisions[$adder]["user"];
        }
    }
    return array("users" => array_count_values($users),
                 "data" => $data);
    /*
    try{
    }catch(Exception $e){
        echo "<!--" . $e . "-->";
        return array("error" => "พบข้อผิดพลาดไม่ทราบสาเหตุ (รายละเอียดเพิ่มเติมดูที่ซอร์ซของไฟล์)");
    }
    * */
    return array("error" => "พบข้อผิดพลาดไม่ทราบสาเหตุ (รายละเอียดเพิ่มเติมดูที่ซอร์ซของไฟล์)");
}
?>
