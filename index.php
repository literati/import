<?php

//require forms lib
require_once dirname(__FILE__) . '/lib/Nibble-Forms/Nibble/NibbleForms/NibbleForm.php';
require_once dirname(__FILE__).'/Parser.php';
require_once dirname(__FILE__).'/Importer.php';


$html = "";
$html.= "<head>";
$html.= '<script src="lib/codemirror/lib/codemirror.js"></script>
<link rel="stylesheet" href="lib/codemirror/lib/codemirror.css">
<script src="lib/codemirror/mode/xml/xml.js"></script>';
$html.="</head>";
echo $html;

//begin PAGE logic
if(empty($_POST['submit'])){
   render_form();
}else{

    $target = $_POST['url'];

    $f = new importer($target);
    $f->fetch();
    $f->save_local();

    $p = new Parser($f->getLocalPath());

    $xml = new DOMDocument();
    $xml->load(importer::FILES_DIR.DIRECTORY_SEPARATOR.'apc-tei-bare.xml');
    $teiBody = $xml->getElementsByTagName('body')->item(0);

    $paras = $p->getParagraphs();
    
    if($paras){
        $i=0;
        foreach($paras as $p){
            $p_class = $p->getAttribute('class');
            if($p_class == 'navline' or $p_class == 'seprline'){
                unset($p);
                continue;
            }
            $p->removeAttribute('class');
            $p->setAttribute('idx', 'p'.$i);
            $p = $xml->importNode($p, true);
            $teiBody->appendChild($p);
            $i++;
        }
    }else{
        echo "no paragraphs found...";  
    }

    $out = $xml->saveXML();
    
    render_form($out);

}



//echo $body->p[0];

function render_form($xml){
    
    $url        = HTML::tag('input', array('type'=>'text', 'name'=>'url'),'');
    $textarea   = HTML::tag('textarea', array('id'=>'xml','name'=>'xml', 'rows'=>'200', 'cols'=>120), $xml);
    $submit     = HTML::tag('input', array('type'=>'submit', 'name'=>'submit'),'',true);
    $elements = $url.$textarea.$submit;
    $form = HTML::tag('form', array('name'=>'test', 'method'=>'post'), $elements);
    
    $codemirror = '<script>CodeMirror.fromTextArea(xml,{mode: "text/xml"
        , lineNumbers: "true"
        , lineWrapping: "true"
        });
        </script>';
    echo $form.$codemirror;

}


class HTML{
    public static function tag($name, $attributes=null, $value=null, $empty=false){
        $html = "";
        $attrs= "";
        if(!empty($attributes)){
            foreach($attributes as $att => $val){
                $attrs .= sprintf("%s=\"%s\"", $att, $val);
            }
        }
        if($empty){
            return sprintf("<%s %s/>", $name, $attrs);
        }
        return sprintf("<%s %s>%s</%s>", $name, $attrs, $value, $name);
    }
}
?>
