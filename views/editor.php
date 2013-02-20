<?php

class view_editor{
    
    
    public static function render_editor_form($xml, $file_to_save, $filename){
        print_r($_POST);
        $codemirror = '<script>CodeMirror.fromTextArea(xml,{mode: "text/xml"
            , lineNumbers: "true"
            , lineWrapping: "true"

            });
            </script>';


        $textarea   = HTML::tag('textarea', array('id'=>'xml','name'=>'xml', 'rows'=>'200', 'cols'=>120), $xml);
        $path       = HTML::tag('input', array('type'=>'hidden', 'name'=>'path', 'value'=>$file_to_save),'',true);
        $name       = HTML::tag('input', array('type'=>'hidden', 'name'=>'filename', 'value'=>$filename),'',true);
        $submit     = HTML::tag('input', array('type'=>'submit', 'name'=>'submit-editor', 'value'=>'Save'),'',true);
        $elements = $path.$name.$textarea.$codemirror."<br/>".$submit;

        $form = HTML::tag('form', array('name'=>'test', 'method'=>'post'), $elements);


        return $form;

    }
    
    
}
?>
