<?php

/**
 * Highest level container class for importing 
 * remote xml and manipulating local copies
 */
class WorkflowManager{
    
    /**
     *
     * @var Workspace 
     */
    public $workspace;
    
    /**
     *
     * @var XMLManager 
     */
    public $xmlMgr;
    
    
    /**
     *
     * @var Importer 
     */
    public $importer;
    
    /**
     * 
     * @param string $url location of some remote content
     * @return string the remote content
     */
    public function import($url){
        $this->importer = new Importer();
        $this->initializeWorkspace($url);
        $data = $this->importer->fetch($url);
        $this->workspace->saveRawFile($data);
    }
    
    
    /**
     * 
     * @param string $url instantiates $this->workspace
     */
    private function initializeWorkspace($url){
        $this->workspace = new Workspace($url);
    }
    
    
    public function presentLevel0Draft(){
        $this->xmlMgr = new XMLManager();
        echo $this->xmlMgr->getLevel0draft($this->workspace->getFilesystemPath($this->workspace->rawFilename));
    }
    
    private function saveRawFile(){
        
    }
    
    public function saveLevel0Draft($data){
        
    }
    
}

/**
 * representation of the working directory and associated 
 * high-level functions
 */
class Workspace{
    
    /**
     *
     * @var string path to current working directory 
     */
    public $cwd;
    
    /**
     *
     * @var string the file name minus extension 
     */
    public $filename;
    
    /**
     *
     * @var FileManager 
     */
    public $filemanager;
    
    
    public $rawFilename;
    
    /**
     * 
     * @param string $url to serve as the basis for 
     * the directory branch and the filename
     */
    public function __construct($url){
        $this->filemanager = new FileManager();
        
        list($raw_file, $filename, $cwd)  = $this->filemanager->decomposePath($url);
        $this->rawFilename = $raw_file;
        $this->filename    = $filename;
        $this->cwd         = $cwd;
        
        $this->filemanager->makeDirRecursive($cwd);
    }
    
    /**
     * 
     * @param string $data content of file
     */
    public function saveRawFile($data){
        $this->filemanager->save($data, $this->cwd.'/'.$this->rawFilename);
    }
    
    public function save(){
        
    }
    
    public function getFilesystemPath($filename){
        return FileManager::NEW_FILES_DIR.'/'.$this->cwd.'/'.$filename;
    }
    
}


/**
 * intermediary between Workspace 
 * and php SPL file libs
 */
class FileManager{
    
    const NEW_FILES_DIR = '/var/www/html/import/newfiles/';
    const TEMPLATES_DIR = '/var/www/html/import/templates/';

    

    public function decomposePath($url){
        $raw_path = $this->trim_proto($url);
        $path_pts = preg_split("#[//]+#", $raw_path);
        $raw_file = array_pop($path_pts);
        $cwd      = implode('/', $path_pts);
        $file_pts = explode('.',$raw_file);
        $ext      = array_pop($file_pts);
        $filename = implode('.', $file_pts);
        
        return array($raw_file, $filename, $cwd);
    }
    
    /**
     * 
     * @param string $path path to be created under the files dir
     * @return bool true on success 
     */
    public function makeDirRecursive($path){
        $path = self::NEW_FILES_DIR.$path;
        return mkdir($path, 0755, true);
    }
    
    public function save($data, $path){
        $path = self::NEW_FILES_DIR.$path;
        if(file_exists($path)){
            return false;
        }
        if(($h = fopen($path, 'w')) !== false){
             if(fwrite($h, $data) === false){
                 die("could not write to file");
             }
        }
    }
    
    /**
      * takes a URL as input and removes the protocol and the www, if present
      * @param string $url
      * @return string
      */
     private function trim_proto($url){
         return str_ireplace(array('http://', 'https://', 'www.'), array(''), $url);
     }
}

/**
 * keeps the cURL stuff safely encapsulated away from other stuff...
 */
class Importer {
    
    public function fetch($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $clean= preg_replace('/&/', '/&amp;', $data); //otherwise, the parser chokes on un-escaped ampersands
        curl_close($ch);
        return $clean;
     }
}


/**
 * wrapper around php SPL XML libs
 */
class XMLManager{

    public function getLevel0draft($path){
        
        $raw        = $this->loadRawHTML($path);
        $template   = $this->loadTemplate();
        
        $cleanHTML  = $this->deClutter($raw, 
                array('class'=>'navline', 'class'=>'seprline'), 
                array('class'));
        $teiBody = $template->getElementsByTagName('body')->item(0);
//        print_r($cleanHTML);
//        die();
        foreach($cleanHTML as $p){
//            $template->importNode($p, true);
            $teiBody->appendChild($p);
        }
        
        return $template->saveXML();
    }
    
    private function loadRawHTML($path){
        
        $doc = new DOMDocument();
        $doc->loadHTMLFile($path);
        $doc->formatOutput=true;
        $body = $doc->getElementsByTagName('body');
        return $body->item(0)->getElementsByTagName('p');
    }
    
    
    
    private function loadTemplate($filename='apc-tei-bare.xml'){
        $xml = new DOMDocument();
        $xml->load(FileManager::TEMPLATES_DIR.$filename);
        return $xml;
    }
    
    private function deClutter($elements, $delete, $remove){
        foreach($elements as $element){
            
            $i=0;
            foreach($delete as $attr => $val){
                if($element->hasAttribute($attr)){
                    if($element->getAttribute($attr) == $val){
                        unset($element);
                        continue;
                    }
                }
            }
            foreach($remove as $attr => $val){
                if(!isset($element)){
                    continue;
                }
                if($element->hasAttribute($attr)){
                    $element->removeAttribute($attr);
                }
            }    
        }
        return $elements;
    }
}

//test that filemanaer decomposes Paths correctly
$f = new FileManager();
print_r($f->decomposePath('http://lsu.edu/index.html'));
print_r($f->decomposePath('http://eapoe.org/works/letters/p3606075.htm'));

$url = 'http://eapoe.org/works/letters/p3606075.htm';

$w = new Workspace($url);


echo "<br/><br/>";

$wk = new WorkflowManager();

$wk->import($url);

$wk->presentLevel0Draft();
?>
