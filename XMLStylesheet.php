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
        $this->countLengths();
    }
    
    private function countLengths(){
        foreach($this->data as $i=>$cells){
            $no = 0;
            foreach($cells as $cell){
                if(@$this->lengths[$no] < strlen($cell)){
                    $this->lengths[$no] = strlen($cell);
                }
                $no++;
            }
        }
        $this->sum = array_sum($this->lengths);
    }

    private function computedStyless(){
        $return = '';
        foreach($this->lengths as $no=>$len){
            $w = ($len/$this->sum)*100;
            $return .= "[no='{$no}'] {width: {$w}%} ";
        }
        
        return $return;
    }
    
    public function __toString() {
        header("Content-type: text/css");
        return "* {font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; font-size: 14px;} row {display: table; width: 100%} row > [field='1'] {display: table-cell; padding: 5px; border: 1px solid #CCCCCC; } row:hover{background: #bbbbbb} ".$this->computedStyless();
    }
    
}
