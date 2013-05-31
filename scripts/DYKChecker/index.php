<h1>เครื่องมือตรวจสอบบทความรู้ไหมว่า | DYK Checker</h1>
<br />
<form method="get" action="#">
    <div class="input-append">
        <?php
            createTextbox(array("class" => "span4",
                                "name" => "title",
                                "id" => "inputTitle",
                                "placeholder" => "กรอกชื่อหน้าตรงนี้",
                                "value" => True));
        ?>
        <input type="submit" value="ตรวจสอบ" class="btn btn-info btn-small"/>
        <input type="button" value="เปิดหน้า" id="openWiki" class="btn btn-primary btn-small"/>
    </div>
</form>
<div id="alertBar" />
<?php
parse_str(getenv("QUERY_STRING"), $_GET);
if(!empty($_GET["title"])){
    include 'core.php';
    $result = connectwp($_GET["title"]);
    if(isset($result["error"])){
        alert("<strong>ผิดพลาด:</strong> ${result['error']}");
    }else{
        echo "<hr>\n";
        echo "<div id='statPanel'>\n";
        $failed = False;
        foreach($result as $k => $v){
            if(!(is_array($v) && array_key_exists("text", $v))) continue;
            echo "<h4><span class='${v["result"]}'>██</span>&nbsp;&nbsp;" . 
                  "${v["text"]}</h4>\n";
            echo "<div>${v["value"]}<br /><br />\n<i>${v["desc"]}</i></div>\n";
            $failed |= ($v["result"] == "failed");
        }
        echo "</div>\n";
        echo "<a id='statbox' class='btn " . ($failed ? "btn-danger disabled'" : 
                    "btn-success' href='//th.wikipedia.org/wiki/วิกิพีเดีย:รู้ไหมว่า/หัวข้อที่ถูกเสนอ'") . 
             ">" . ($failed ? "ไม่ผ่าน" : "ผ่าน") . "</a>";
        echo "<br />\n";
        echo "<pre>\n" . $result["newtext"] . "</pre>\n";
    }
}
?>
