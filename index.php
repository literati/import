<?php

require_once 'lib.php';

if(isset($_POST['submit-editor'])){
    //save the level 0 version
    echo HTML::head();
    
    $wk = unserialize($_POST['workflow']);
    $xml = $_POST['xml'];
    if($wk->saveLevel0Draft($xml)){
        header('Location: test.php');
    }else{
        die('file save failed');
    }
}elseif(isset($_POST['submit-url'])){
    //import the file at url and display the editor
    $url = $_POST['url'];
    $codemirror_head = '<script src="lib/codemirror/lib/codemirror.js"></script>
        <link rel="stylesheet" href="lib/codemirror/lib/codemirror.css">
        <script src="lib/codemirror/mode/xml/xml.js"></script>';
    
    echo HTML::head($codemirror_head);
    $wk = new WorkflowManager();
    $wk->import($url);
    $xml = $wk->presentLevel0Draft();
    
    echo FormsManager::render_editor_form($xml, serialize($wk));
}else{
    //just render the url form
    echo HTML::head();
    echo FormsManager::render_url_form();
}


?>
