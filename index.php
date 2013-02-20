<?php

//require forms lib
require_once dirname(__FILE__) . '/lib/Nibble-Forms/Nibble/NibbleForms/NibbleForm.php';
require_once dirname(__FILE__).'/Parser.php';
require_once dirname(__FILE__).'/Importer.php';
require_once dirname(__FILE__).'/views/editor.php';
require_once dirname(__FILE__).'/views/url.php';
require_once dirname(__FILE__).'/views/html.php';


$html = "";
$html.= "<head>";
$html.= '<script src="lib/codemirror/lib/codemirror.js"></script>
<link rel="stylesheet" href="lib/codemirror/lib/codemirror.css">
<script src="lib/codemirror/mode/xml/xml.js"></script>';
$html.="</head>";
echo $html;

//begin PAGE logic
if(isset($_POST['submit-url'])){

    $target = $_POST['url'];

    //get the file, save a local copy
    $f = new importer($target);
    $f->fetch();
    Importer::save_local($f->local_path, $f->file_name_orig, $f->data);

    //get the local file, manipulate and save as...
    $p = new Parser($f->getLocalPath($f->file_name_orig));

    $xml = new DOMDocument();
    $xml->load(importer::FILES_DIR.DIRECTORY_SEPARATOR.'apc-tei-bare.xml');
    $teiBody = $xml->getElementsByTagName('body')->item(0);

    
    $i=0;
    foreach($p->getParagraphs() as $p){
        $p_class = $p->getAttribute('class');
        if($p_class == 'navline' or $p_class == 'seprline'){
            unset($p);
            continue;
        }
        $p->removeAttribute('class');
//            $p->setAttribute('idx', 'p'.$i);
        $p = $xml->importNode($p, true);
        $teiBody->appendChild($p);
        $i++;
    }
   
    
    echo view_editor::render_editor_form($xml->saveXML(), $f->local_path, $f->file_name);

}elseif(isset($_POST['submit-editor'])){
    $path = $_POST['path'];
    $data = $_POST['xml'];
    $name = $_POST['filename'];
    $ext  = ".level-0.xml";
    
    importer::save_local($path, $name.$ext, $data);
    echo "saving to ".$name." ".$data." ". $path.$ext;
//    header('Location: index.php');
    
    
}else{
    echo view_url::render_url_form();
}



?>
