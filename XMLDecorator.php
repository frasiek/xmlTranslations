<?php

/**
 * Translates array to html
 *
 * @author frasiek
 */

namespace mfXML;

class XMLDecorator {

    public function decorate(&$response) {
        $this->setXmlHeaders();
        $response = $this->xmlProlog()."<root>".$this->toXml($response)."</root>";
    }
    
    private function setXmlHeaders(){
        header("Content-Type: application/xml; charset=utf-8");
    }
    
    private function xmlProlog(){
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n"."\n".'<?xml-stylesheet href="index.php?a=xslt" type="text/xsl" ?>';
    }
    
    private function toXml($data){
        $return = "";
        
        foreach($data as $row){
            $return .= "<row>";
            $i = 0;
            foreach($row as $cellName => $cell){
                $return .= "<{$cellName} field='1' no='".($i++)."'><![CDATA[{$cell}]]></{$cellName}>";
            }
            $return .= "</row>";
        }
        return $return;
    }
    
    

    
}
