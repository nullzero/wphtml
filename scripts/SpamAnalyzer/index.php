<h1>เครื่องมือวิเคราะห์สแปม | SpamAnalyzer</h1>
<br />
<form method="get" action="#">
    <div class="input-append">
        <?php
            createTextbox(array("class" => "span4",
                                "name" => "title",
                                "id" => "inputTitle",
                                "placeholder" => "กรอกยูอาร์แอลตรงนี้",
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
                    <h2>สรุปข้อมูลเบื้องต้น</h2>
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
                </div>
                <?php
                    for($i = 1; $i <= count($data); ++$i){
                        echo "<div class='tab-pane' id='navtab-${i}'>";
                        difffun($data[$i - 1]["text1"], $data[$i - 1]["text2"]);
                        echo "</div>";
                    }
                ?>
            </div>
            
        </div>
<?php
    }
}
?>
