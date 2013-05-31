<?php
include 'lib/wp/lib.php';

function connectwp($url){
    $site = new Site("th");
    $data = array();
    $user = array();
    foreach($site->exturlusage($url, 15) as $item){
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
        $cnt = 0;
        foreach($revisions as $rev){
            if($revisions[$adder]["user"] == $rev["user"]){
                $cnt++;
            }
        }
        if($adder == count($revisions) - 1){
            $data[] = array("error" => "beyond limit or at start");
        }else{
            $data[] = array("user" => $revisions[$adder]["user"],
                            "timestamp" => $revisions[$adder]["timestamp"],
                            "title" => $item["title"],
                            "url" => $item["url"],
                            "edits" => $cnt,
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
