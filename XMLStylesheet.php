<?php

/**
 * Show stylesheet for xml file
 *
 * @author frasiek
 */
namespace mfXML;
class XMLStylesheet {
    
    protected $data;
    protected $lengths = array();
    protected $sum = 0;
    
    function __construct($data) {
        $this->data = $data;
    }
    
    public function __toString() {
        header("Content-type: text/css");
        return "* {font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; font-size: 14px;} tr:hover td{background: #bbbbbb} ";
    }
    
}
