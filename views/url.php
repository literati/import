<?php

class view_url{
    
    public static function render_url_form(){
        $url        = HTML::tag('input', array('type'=>'text', 'name'=>'url'),'');
        $submit     = HTML::tag('input', array('type'=>'submit', 'name'=>'submit-url','value'=>'Fetch'),'',true);
        $elements = $url.$submit;
        $form = HTML::tag('form', array('name'=>'url', 'method'=>'post'), $elements);
        return $form;
    }
}
?>
