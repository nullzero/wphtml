<?php
global $f1, $f2;
if(isset($_GET["title"]) or (empty($_POST["content"]) and empty($_GET["content"]))){
    $f1 = "active";
    $f2 = "";
}else{
    $f1 = "";
    $f2 = "active";
}
?>
<h1>เครื่องมือแปลลิงก์วิกิพีเดีย | WikiTranslator</h1>
<h5><span style="color: red">(โปรดทราบว่าวิกิพีเดียภาษาไทยไม่รับบทความภาษาต่างประเทศ
บทความที่แปลผ่านเครื่องมือนี้และไม่มีการแปลเพิ่มเติมจนเป็นภาษาไทยทั้งหมดจะถูกลบอย่างแน่นอนหากส่งเข้าเนมสเปซหลัก)
</span></h5>
<br />
<form id="form" method="post" action="">
<div class="row">
    <div class="span2 form-inline">
        <div class="form-inline">
            <div class="span1">
                <label>รหัสไซต์<br />ปลายทาง</label>
            </div>
            <?php
            createTextbox(array("style" => "width: 50px",
                                "name" => "siteDest",
                                "id" => "inputSiteDest",
                                "placeholder" => "เช่น th",
                                "value" => True)); 
            ?>
        </div>
        <br />
        <div class="form-inline">
            <div class="span1">
                <label>รหัสไซต์<br />ต้นทาง</label>
            </div>
            <?php
            createTextbox(array("style" => "width: 48px",
                                "name" => "site",
                                "id" => "inputSite",
                                "placeholder" => "เช่น en",
                                "value" => True)); 
            ?>
        </div>
    </div>
    <div class="span8 form-inline vbarr vbarl">
        <div class="tabbable">
            <ul class="nav nav-tabs">
                <li class="<?php global $f1; echo $f1 ?>">
                    <a id="ausepage" href="#usepage" data-toggle="tab">ใช้ชื่อหน้า</a>
                </li>
                <li class="<?php global $f2; echo $f2 ?>">
                    <a id="ausecontent" href="#usecontent" data-toggle="tab">ใช้ข้อความ</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane <?php global $f1; echo $f1 ?>" 
                     id="usepage" style="text-align: center">
                    <?php
                    createTextbox(array("class" => "span3",
                                        "name" => "title",
                                        "id" => "inputTitle",
                                        "placeholder" => "ชื่อหน้าในไซต์ต้นทาง",
                                        "value" => True));
                    ?>
                </div>
                <div class="tab-pane <?php global $f2; echo $f2 ?>" id="usecontent">
                    <textarea id="inputContent" name="content" rows="10" 
                              class="input-block-level" 
                              placeholder="ข้อความที่จะให้แปล"><?php 
                        if(isset($_POST['content'])){
                            echo $_POST['content'];
                        }else if(isset($_GET['content'])){
                            echo $_GET['content'];
                        }
                    ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="span1">
        <input id="btnSubmit" type="submit" value="&nbsp;&nbsp;แปล!&nbsp;&nbsp;" 
               class="btn btn-info" style="height: 50px;"/>
        <input style="margin-top: 5px" type="button" id="btnClear" class="btn" 
               value="เริ่มใหม่"/>
    </div>
</div>
</form>
<div id="alertBar" />
<?php
parse_str(getenv("QUERY_STRING"), $_GET);
if((!empty($_GET["title"]) or !empty($_POST["content"]) or !empty($_GET["content"]) and 
                              !empty($_GET["siteDest"]) and 
                              !empty($_GET["site"]))){
    if(empty($_GET["title"])) $_GET["title"] = Null;
    if(empty($_POST["content"])){
        if(empty($_GET["content"])){
            $_POST["content"] = Null;
        }else{
            $_POST["content"] = $_GET["content"];
        }
    }
    include 'core.php';
    $result = connectwp($_GET["title"], $_GET["site"], $_GET["siteDest"], $_POST["content"]);
    if(isset($result["error"])){
        alert("<strong>ผิดพลาด:</strong> ${result['error']}");
    }else{
        echo "<hr>\n";
        echo "<pre>\n" . $result["text"] . "</pre>\n";
    }
}else{
    if(isset($_GET["title"]) || isset($_GET["content"]) || isset($_POST["title"]) || 
                                isset($_GET["site"]) || isset($_GET["siteDest"])){
        alert("<strong>ผิดพลาด:</strong> โปรดระบุข้อมูลให้ครบถ้วน");
    }
}
?>
