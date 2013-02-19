<?php

class Parser {
    
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

?>
