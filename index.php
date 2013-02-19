<?php

//require forms lib
require_once dirname(__FILE__) . '/lib/Nibble-Forms/Nibble/NibbleForms/NibbleForm.php';
require_once dirname(__FILE__).'/Parser.php';
require_once dirname(__FILE__).'/Importer.php';


//begin PAGE logic
if(empty($_POST['nibble_form'])){
   render_form();
}else{

    $target = $_POST['nibble_form']['source_url'];

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
        foreach($p->getParagraphs() as $p){
            $p_class = $p->getAttribute('class');
            if($p_class == 'navline' or $p_class == 'seprline'){
                unset($p);
                continue;
            }
            $p->removeAttribute('class');
            $p->setAttribute('id', 'p'.$i);
            $p = $xml->importNode($p, true);
            $teiBody->appendChild($p);
            $i++;
        }
    }else{
        echo "no paragraphs found...";  
    }

    echo $xml = $xml->saveXML();

    render_form($xml);

}



//echo $body->p[0];

function render_form($xml){
    /* Get an instance of the form called "form_one" */
    $form = \Nibble\NibbleForms\NibbleForm::getInstance('form_one');
    /* Add field using 3 arguments; field name, field type and field options */
    $form->addField('source_url', 'url', array('required' => true));
    $form->addField('xml', 'TextArea', 
            array('required' => true
                    ,'rows'  => 200
                    ,'cols'  => 150
                    ,'value' => $xml));

    echo $form->render();

}
?>
