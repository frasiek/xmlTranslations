<?php

/**
 * @author frasiek
 */

namespace mfXML;

class XSLT {

    protected $data;
    protected $tests;
    protected $sorting;
    protected $colNames;
    
    function __construct($data, $tests, $sorting) {
        $this->data = $data;
        $this->colNames = array_keys($data[0]);
        $this->tests = $tests;
        $this->sorting = $sorting;
    }

    private function getXSLT() {
        $header = "";
        $content = "";
        foreach($this->colNames as $column){
            $header .= "<th><a href='#' data-field='{$column}' class='sorting'>{$column}</a><br/><input type='text' class='form-control xslt-filter' data-columnValue='{$column}'/></th>";
            $content .= "<td><xsl:value-of select='{$column}'/></td>";
        }
        $sorting = '';
        if($this->sorting){
            $sorting = '<xsl:sort select="'.$this->sorting.'"/>';
        }
        $test = '';
        $testend = '';
        if($this->tests){
            $test = '<xsl:if test="'.$this->tests.'">';
            $testend = '</xsl:if>';
        }
         $xslt = 
<<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" encoding="utf-8" indent="yes" doctype-system="about:legacy-compat"/>
                 
<xsl:template match="/">
  <html xmlns="http://www.w3.org/1999/xhtml">
    <link media="all" href="css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link media="all" href="css/bootstrap-theme.min.css" rel="stylesheet" type="text/css"/>
    <link media="all" href="index.php?a=stylesheet" rel="stylesheet" type="text/css"/>
    <meta charset="utf-8"/>
    <title>XML XSLT</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <body>
  <table class="table">
    <tr>
      {$header}
    </tr>
    <xsl:for-each select="root/row">
      {$sorting}
      {$test}
        <tr>
          {$content}
        </tr>
      {$testend}
    </xsl:for-each>
  </table>
  </body>
  </html>
</xsl:template>

</xsl:stylesheet>
EOD;
         return $xslt;
    }

    public function __toString() {
        header("Content-type: text/xsl");
        return $this->getXSLT();
    }

}
