<?php
/**
 * Manager for mysql connection via AJAX request
 *
 * @author frasiek
 */

namespace mfXML;

require_once 'Query.php';
require_once 'HtmlDecorator.php';
require_once 'XMLDecorator.php';
require_once 'XMLStylesheet.php';
require_once 'XSLT.php';

class Index {

    protected $query;
    protected $decorators = array();
    protected $response = false;
    protected $session = array();
    protected $action = 'index';
    protected $isPlain = false;
    
    const SESSION_NAMESPACE = 'mfXML';
    const DB_HOST = 'DB_HOST';
    const DB_USER = 'DB_USER';
    const DB_PASSWORD = 'DB_PASSWORD';
    const DB_DB = 'DB_DB';
    const DB_TABLE = 'tableName';

    function __construct() {
        session_start();
        $this->setErrorReporting();
        $this->readSession();
        $this->readRequest();
        try {
            @$this->query = new Query($this->session[self::DB_HOST], $this->session[self::DB_USER], $this->session[self::DB_PASSWORD], $this->session[self::DB_DB]);
        } catch (\Exception $ex) {
            $this->response['ERROR'] = $ex->getMessage();
            header("HTTP/1.0 500 ".$ex->getMessage());
        }
    }

    public function isPlain(){
        return $this->isPlain;
    }
    
    public function getResponse() {
        if (!$this->isAjax() && !$this->isPlain()) {
            $this->decorators[] = new HtmlDecorator();
        }

        $this->saveSession();
        return $this->getResponseArray();
    }

    public function isAjax() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    protected function readSession() {
        $this->session = $_SESSION[self::SESSION_NAMESPACE];
    }

    protected function writeSession($key, $val) {
        $this->session[$key] = $val;
        $this->saveSession();
    }

    protected function saveSession() {
        $_SESSION[self::SESSION_NAMESPACE] = $this->session;
    }
    
    protected function clearSession($key){
        unset($_SESSION[self::SESSION_NAMESPACE][$key]);
        unset($this->session[$key]);
        $this->saveSession();
    }

    protected function readRequest() {
        if (isset($_REQUEST['a'])) {
            $this->action = $_REQUEST['a'];
            switch($_REQUEST['a']) {
                case 'connect':
                    $this->writeSession(self::DB_DB, $_REQUEST[self::DB_DB]);
                    $this->writeSession(self::DB_HOST, $_REQUEST[self::DB_HOST]);
                    $this->writeSession(self::DB_PASSWORD, $_REQUEST[self::DB_PASSWORD]);
                    $this->writeSession(self::DB_USER, $_REQUEST[self::DB_USER]);
                    break;
                case 'showContent':
                    $this->isPlain = true;
                    $this->decorators[] = new XMLDecorator();
                    $this->writeSession(self::DB_TABLE, $_REQUEST[self::DB_TABLE]);
                    break;
                case 'stylesheet':
                case 'xslt':
                    $this->isPlain = true;
                    break;
                case 'sorting':
                    $this->writeSession("sorting", $_REQUEST['field']);
                    exit(0);
                case 'tests':
                    $field = $_REQUEST['field'];
                    $test = $_REQUEST['test'];
                    if(strpos($test, ">")!==false){
                        $test = str_replace(">", "&gt;", $test);
                    } elseif(strpos($test, "<")!==false){
                        $test = str_replace("<", "&lt;", $test);
                    } elseif(strpos($test, "=")===false){
                        $test = "=".$test;
                    }
                    $this->writeSession("tests", $field.$test);
                    exit(0);
            }
        }
    }

    protected function getResponseArray() {
        $response = array();
        if ($this->response !== false) {
            $response = $this->response;
        } else {
            $response = $this->createResponse();
        }

        foreach ($this->decorators as &$decorator) {
            $decorator->decorate($response);
        }
        unset($decorator);

        return $response;
    }

    protected function createResponse() {
        switch($this->action){
            case 'connect':
                return array("Success"=>"Connected");
            case 'fetchTables':
                $this->clearSession("sorting");
                $this->clearSession("tests");
                return $this->query->rawQuery("show tables", true);
            case 'showContent':
                return $this->query->select("*")->from("`".$this->session[self::DB_TABLE]."`")->get();
            case 'stylesheet':
                $this->action = 'showContent';
                $data = $this->createResponse();
                echo new XMLStylesheet($data);
                exit(0);
            case 'xslt':
                $this->action = 'showContent';
                $data = $this->createResponse();
                echo @new XSLT($data, $this->session['tests'], $this->session['sorting']);
                exit(0);
                
        }
    }

    protected function setErrorReporting() {
        error_reporting(false);
        ini_set("display_errors", 0);
    }

}

$manager = new Index();

if ($manager->isAjax()) {
    echo json_encode(array('response' => $manager->getResponse()));
    exit(0);
}
if ($manager->isPlain()) {
    echo $manager->getResponse();
    exit(0);
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>XML XSLT</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>

        <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" media="all"/>
        <link type="text/css" rel="stylesheet" href="css/bootstrap-theme.min.css" media="all"/>
        <link type="text/css" rel="stylesheet" href="css/main.css" media="all"/>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/main.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-default" role="navigation">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">DB <span class="glyphicon glyphicon-arrow-right"></span> XML + XSLT</a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Connection <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="#" id="setUpConnection">Set</a></li>
                                <li class="divider"></li>
                                <li><a href="#" id="fetchTables">Fetch tables</a></li>
                            </ul>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
        <div id="mainWrapper" class="container-fluid container ">
            <?php echo $manager->getResponse() ?>
        </div>
        <div id="dynamicContent" class="container-fluid container ">

        </div>
    </body>
</html>