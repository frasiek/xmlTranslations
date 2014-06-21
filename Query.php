<?php

/*
 * Query
 * @author Michał Fraś
 */
require_once (__DIR__ . "/config.php");

class Query {

    protected $_distinct = false;
    protected $_select = array();
    protected $_from = array();
    protected $_joins = array();
    protected $_where = array();
    protected $_groupBy = array();
    protected $_orderBy = array();
    protected $_limit = false;
    protected $_offset = false;
    protected $_countRows = false;
    protected $_having = array();
    protected $_countedRows = 0;
    protected static $DB;

    function __construct() {
        if(!self::$DB){
            self::$DB = mysql_connect(DB_HOST, DB_USER, DB_PASS);
            mysql_query("USE " . DB_DB, self::$DB);
            mysql_set_charset("utf8", self::$DB);
        }
    }
    
    public static function closeConnections() {
        if(self::$DB){
            mysql_close(self::$DB);
        }
    }

    public function __call($name, $params) {
        if (strpos($name, "clear") === 0) {
            $partName = "_" . strtolower(str_replace("clear", "", $name));
            if (property_exists(get_called_class(), $partName)) {
                if (is_array($this->$partName)) {
                    $this->$partName = array();
                } else {
                    $this->$partName = false;
                }
                return true;
            }
        }
        throw new Exception("Property doesn't exist");
    }

    /**
     * Ustawia flage czy to ma byc SELECT DISTINC
     * @param bool $_distinct
     * @return CSchemaBuilder_Query
     */
    public function distinct($_distinct = true) {
        $this->_distinct = $_distinct;
        return $this;
    }

    public function setCountRows($countRows = true) {
        $this->_countRows = $countRows;
        return $this;
    }

    /**
     * Ustawia limit
     * @param int $limit
     * @return CSchemaBuilder_Query
     */
    public function Limit($limit) {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * ustawia offset
     * @param int $offset
     * @return CSchemaBuilder_Query
     */
    public function offset($offset) {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * Ustawia pole SELECT ... czyli wybierane pola
     * @param string $_select
     * @return CSchemaBuilder_Query
     */
    public function select($_select) {
        $this->_select[$_select] = $_select;
        return $this;
    }

    /**
     * Ustawia FROM ... tabele zrodlowa
     * @param string $_from
     * @return CSchemaBuilder_Query
     */
    public function from($_from) {
        $this->_from[$_from] = $_from;
        return $this;
    }

    /**
     * alias dla CSchemaBuilder_Query::joins
     * @see CSchemaBuilder_Query::joins
     */
    public function join($_join) {
        return $this->joins($_join);
    }

    /**
     * Dodaje JOINA trzeba dodac calego, razem z typem np. INNER JOIN, LEFT JOIN
     * @param string $_joins
     * @return \CSchemaBuilder_Query
     */
    public function joins($_joins) {
        $this->_joins[$_joins] = $_joins;
        return $this;
    }

    /**
     * Dodaje whera, z laczeniem jako AND
     * @param string $_where
     * @return CSchemaBuilder_Query
     */
    public function where($_where) {
        $this->_where[$_where] = $_where;
        return $this;
    }

    /**
     * Dodaje having, z laczeniem jako AND
     * @param string $_where
     * @return CSchemaBuilder_Query
     */
    public function having($_where) {
        $this->_having[$_where] = $_where;
        return $this;
    }

    /**
     * dodaje grupowanie
     * @param string $_groupBy
     * @return CSchemaBuilder_Query
     */
    public function groupBy($_groupBy) {
        $this->_groupBy[$_groupBy] = $_groupBy;
        return $this;
    }

    /**
     * dodaje order by
     * @param string $_orderBy
     * @return CSchemaBuilder_Query
     */
    public function orderBy($_orderBy) {
        $this->_orderBy[$_orderBy] = $_orderBy;
        return $this;
    }

    /**
     * Zwraca dane lub zapytanie
     * @param bool $execute dla true zwraca dane, false zwraca kwerende
     * @param type $throw czy ma rzucac wyjatek, czy umierac z wypisaniem kwerendy
     * @return mixed tablica wynikow lub string
     * @throws Exception jezeli throw jest ustawione na true oraz execute na true a kwerenda jest nie poprawna
     */
    public function get($execute = true, $throw = false) {
        $query = "SELECT ";
        if ($this->_distinct) {
            $query .=" DISTINCT \n";
        } else {
            $query .= "\n";
        }

        if ($this->_countRows) {
            $query .=" SQL_CALC_FOUND_ROWS \n";
        }

        $query .= "\t" . implode(", \n\t", $this->_select) . "\n";
        $query .= "FROM \n";
        $query .= "\t" . implode(", ", $this->_from) . "\n";
        if (count($this->_joins)) {
            $query .= "\t" . implode(" \n", $this->_joins) . "\n";
        }
        if (count($this->_where)) {
            $query .= "WHERE \n";
            $query .= "\t" . implode(" AND ", $this->_where) . "\n";
        }
        if (count($this->_groupBy)) {
            $query .= "GROUP BY \n";
            $query .= "\t" . implode(",", $this->_groupBy) . "\n";
        }
        if (count($this->_having)) {
            $query .= "HAVING \n";
            $query .= "\t" . implode(" AND ", $this->_having) . "\n";
        }
        if (count($this->_orderBy)) {
            $query .= "ORDER BY \n";
            $query .= "\t" . implode(",", $this->_orderBy) . "\n";
        }
        if ($this->_limit) {
            $query .= "LIMIT {$this->_limit}\n";
        }
        if ($this->_offset) {
            $query .= "OFFSET {$this->_offset}\n";
        }
        
        if (!$execute){
            return $query;
        }
        try {
            $result = mysql_query($query, self::$DB);
            if(!$result){
                return false;
            }
            
            $data = array();
            while ($tmp = mysql_fetch_assoc($result)) {
                $data[] = $tmp;
            }
            if ($this->_countRows) {
                $tmp = mysql_query("SELECT FOUND_ROWS() as r;", self::$DB);
                $tmp = mysql_fetch_assoc($tmp);
                $this->_countedRows = $tmp['r'];
            }
            return $data;
        } catch (Exception $ex) {
            if ($throw)
                throw $ex;
            echo "<h1>Błąd kwerendy!</h1>";
            echo "<h2>{$ex->getMessage()}</h2>";
            echo "<pre>{$this->get(false)}</pre>";
            die;
        }
    }
    
    /**
     * Zwraca pierwszy rekord lub zapytanie
     * @param bool $execute dla true zwraca dane, false zwraca kwerende
     * @param type $throw czy ma rzucac wyjatek, czy umierac z wypisaniem kwerendy
     * @return mixed tablica wynikow lub string
     * @throws Exception jezeli throw jest ustawione na true oraz execute na true a kwerenda jest nie poprawna
     */
    public function getOne($execute = true, $throw = false) {
        $data = $this->get($execute,$throw);
        return $data[0];
    }
    
    public static function rawQuery($query){
        if(!self::$DB){
            new Query();//inicjalizacja polaczenia
        }
        mysql_query($query,self::$DB);
    }
    
    public function strip($param){
        if($param === null || $param === "null" || $param === ''){
            return "NULL";
        }
        if(is_numeric($param)){
            return $param;
        }
        return "'".addslashes($param)."'";
    }

    public function getTotalCount() {
        return $this->_countedRows;
    }

    public function debug() {
        echo "<pre>" . $this->get(false);
        die;
    }

}
