<?php
require_once "lib/Diff.php";
require_once "lib/Diff/Renderer/Html/SideBySide.php";

function difffun($a, $b){
    $diff = new Diff(explode("\n", $a), explode("\n", $b), array());
    $renderer = new Diff_Renderer_Html_SideBySide;
    echo $diff->Render($renderer);
}

?>
