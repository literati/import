<?php

//require forms lib
require_once dirname(__FILE__) . '/lib/Nibble-Forms/Nibble/NibbleForms/NibbleForm.php';

class importer {
    
    const FILES_DIR = 'files';
    
    public $data;
    public $remote;
    public $file_name;
    public $local_path;
    
    public function __construct($url){
        $this->remote = $url;
        
        $comps = preg_split("#[//]+#", $this->trim_proto($this->remote));
        $this->file_name = array_pop($comps);
        $this->local_path = implode('/', $comps);
    }
    
 public function fetch(){
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, $this->remote);
     curl_setopt($ch, CURLOPT_HEADER, false);
     curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
     $this->data = curl_exec($ch);
     curl_close($ch);
     }

     private function graft_fs($url){
         
     }
     
     /**
      * takes a URL as input and removes the protocol and the www, if present
      * @param string $str
      * @return string
      */
     private function trim_proto($str){
         return str_ireplace(array('http://', 'https://', 'www.'), array(''), $str);
     }
     
     
     /**
      * saves the fetched file in the local filesystem in a path resembling the original url
      * NB: this can only be called once per file, otherwise, we will trim too much of the path
      */
     public function save_local(){
         $target = self::FILES_DIR.DIRECTORY_SEPARATOR.$this->local_path;
         
         if(is_dir($target) or mkdir($target, 0755, true)){
             if(($h = fopen($target.DIRECTORY_SEPARATOR.$this->file_name, 'w')) !== false){
                 if(fwrite($h, $this->data) === false){
                     die("could not write to file");
                 }
             }
         }
     }
     
     public function save_tei(){
         
     }
     
     public function getLocalPath(){
         return self::FILES_DIR.DIRECTORY_SEPARATOR.$this->local_path.DIRECTORY_SEPARATOR.$this->file_name;
     }
     
}


class parser {
    
    public $source;
    public $doc;
    public $body;
    public $output;
    
    public function __construct($infile){
        $this->source = $infile;
        $this->doc = new DOMDocument();
        $this->doc->loadHTMLFile($this->source);
        $this->body = $this->doc->getElementsByTagName('body');
    }
    
    public function getParagraphs(){
        return $this->body->item(0)->getElementsByTagName('p');
    }
     
}


//begin PAGE logic
if(empty($_POST['nibble_form'])){
   render_form();
}else{

    $target = $_POST['nibble_form']['source_url'];

    $f = new importer($target);
    $f->fetch();
    $f->save_local();


    $p = new parser($f->getLocalPath());

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

    echo $xml->saveXML();


    render_form();

}



//echo $body->p[0];

function render_form(){
    /* Get an instance of the form called "form_one" */
    $form = \Nibble\NibbleForms\NibbleForm::getInstance('form_one');
    /* Add field using 3 arguments; field name, field type and field options */
    $form->addField('source_url', 'url', array('required' => false));

    echo $form->render();

}
?>
