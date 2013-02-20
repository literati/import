<?php
class importer {
    
    const FILES_DIR = 'files';
    
    public $data;
    public $remote;
    public $file_name_orig;
    public $file_name;
    public $local_path;
    
    public function __construct($url){
        $this->remote = $url;
        
        $comps = preg_split("#[//]+#", $this->trim_proto($this->remote));
        $this->file_name_orig = array_pop($comps);
        $this->local_path = implode('/', $comps);
        
        if(strlen($this->file_name_orig) == 0){
            $this->file_name_orig = 'index.html';
        }
        $name_parts = explode('.', $this->file_name_orig); //won't catch filenames with more than one dot
        $this->file_name = array_shift($name_parts);
        
        print_r($this);
        die();
    }
    
    public function fetch(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->remote);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $clean= preg_replace('/&/', '/&amp;', $data); //otherwise, the parser chokes on un-escaped ampersands
        $this->data = $clean;
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
     public static function save_local($path, $filename, $data){
         $target = self::FILES_DIR.DIRECTORY_SEPARATOR.$path;
         
         if(is_dir($target) or mkdir($target, 0755, true)){
             if(($h = fopen($target.DIRECTORY_SEPARATOR.$filename, 'w')) !== false){
                 if(fwrite($h, $data) === false){
                     die("could not write to file");
                 }
             }
         }
     }
     
     public function save_tei(){
         
     }
     
     public function getLocalPath($filename){
         return self::FILES_DIR.DIRECTORY_SEPARATOR.$this->local_path.DIRECTORY_SEPARATOR.$filename;
     }
     
}
?>
