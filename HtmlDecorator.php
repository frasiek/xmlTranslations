<?php

/**
 * Translates array to html
 *
 * @author frasiek
 */

namespace mfXML;

class HtmlDecorator {

    public function decorate(&$response) {
        $response = $this->arrayToTable($response);
    }

    private function arrayToTable($array){
        $tmp = '';
        if(is_array($array)){
            foreach($array as $name=>$val){
                $tmp = '<div class="alert alert-info alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
                $tmp .= "<strong>{$name}: </strong>";
                if(is_array($val)){
                    $tmp .= "</div>";
                    $tmp .= $this->arrayToTable($val);
                } else {
                    $tmp .= "{$val}";
                    $tmp .= "</div>";
                }
            }
        }
        
        return $tmp;
    }
}
