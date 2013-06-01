<h1>เครื่องมือวิเคราะห์สแปม | SpamAnalyzer</h1>
<br />
<form method="get" action="#">
    <div class="input-append">
        <?php
            createTextbox(array("class" => "span4",
                                "name" => "title",
                                "id" => "inputTitle",
                                "placeholder" => "กรอกยูอาร์แอลตรงนี้ ไม่ต้องมีโพรโตคอลนำหน้า",
                                "value" => True));
        ?>
        <input type="submit" value="ตรวจสอบ" class="btn btn-info btn-small"/>
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
        include 'lib/php-diff/difflib.php';
        $data = $result["data"];
?>
        <hr>
        <span style="color: red">หมายเหตุ: สำหรับแต่ละหน้า โปรแกรมจะตรวจสอบเฉพาะการแก้ไข 500 รายการล่าสุดเท่านั้น</span>
        <br />
        <br />
        <div class="tabbable">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#navtab-main" data-toggle="tab">Overall</a>
                </li>
                <?php
                    for($i = 1; $i <= count($data); ++$i){
                        echo "<li><a href='#navtab-${i}' data-toggle='tab'>${i}</a></li>";
                    }
                ?>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="navtab-main">
                    <h3>สรุปข้อมูลเบื้องต้น</h3>
                    พบการเพิ่มลิงก์ทั้งหมด <?php echo count($dat); ?> รายการ
                    <br />
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ชื่อผู้ใช้</th>
                                <th>การแก้ไขในหน้าที่แตกต่างกัน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach($result["users"] as $key => $val){
                                echo "<tr><td>${key}</td><td>${val}</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <br />
                    <br />
                    <h4>หน้าที่ตรวจสอบการเพิ่มลิงก์ไม่ได้</h4>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>หน้า</th>
                                <th>ยูอาร์แอล</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach($result["fail"] as $key => $val){
                                echo "<tr><td>${key}</td><td>${val}</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <br />
                    <br />
                    มีการเพิ่มลิงก์ที่ไม่นับ (เช่นโดยบอต) ทั้งหมด <?php echo $result["cntopt"]; ?> ครั้ง
                </div>
                <?php
                    for($i = 1; $i <= count($data); ++$i){
                        echo "<div class='tab-pane' id='navtab-${i}'>";
                        $dat = $data[$i - 1];
                        if(array_key_exists("error", $dat)){
                            echo "โปรแกรมได้รับรายงานมาว่ามีการเพิ่มลิงก์ ${dat['url']} ในหน้า " .
                                 "${dat['title']} แต่กลับ (1) ไม่พบลิงก์ดังกล่าวในหน้านั้น หรือ " . 
                                 "(2) การเพิ่มลิงก์นั้นเกิดขึ้นก่อนหน้าการแก้ไข 500 รายการล่าสุด " . 
                                 "โปรดตรวจสอบหน้านี้ด้วยตนเอง";
                            echo "</div>"; // close tag
                            continue;
                        }
                        echo "ผู้ใช้ ${dat['user']} เพิ่มลิงก์ ${dat['url']} ที่หน้า ${dat['title']} " . 
                             "เมื่อ ${dat['timestamp']} โดยมีการแก้ไขก่อนหน้าการเพิ่มลิงก์นี้ " .
                             "${dat['edits']} ครั้ง<br />\n<br />\n<br />\n";
                        difffun($dat["text1"], $dat["text2"]);
                        echo "</div>";
                    }
                ?>
            </div>
            
        </div>
<?php
    }
}else{
    /*
    include 'lib/php-diff/difflib.php';
    difffun("| แนวเพลง = [[ร็อคแอนด์โรลล์]]", "| แนวเพลง = ร็อคแอนด์โรลล์");
    * */
}
?>
