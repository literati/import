<?php

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
