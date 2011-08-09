<?php
/*
 * Copyright (C) 2011 Richard Kakaš.
 * All rights reserved.
 * Contact: Richard Kakaš <richard.kakas@gmail.com>
 *
 * @LICENSE_START@
 * This file is part of CFD project and it is licensed
 * under license that is described in CFD project's LICENSE file.
 * See LICENSE file for information about how you can use this file.
 * @LICENSE_END@
 */

namespace cfd\core;

// in this file there are MySql*Query classes + MySqlQueryResult class which are private
// and can be created only by DbSpecificDriver::createSpecificQuery() function or by
// query() function

/**
 * @brief Database driver for MySQL.
 *
 * This class implements \\cfd\\core\\DbSpecificDriver interface
 * for MySQL database system.
 *
 * Specific arguments for $driverArgs array in connect() function:
 * @code
 *  For now nothing.
 * @endcode
 *
 * @see \\cfd\\core\\DbDriver
 */
class MySqlSpecificDriver implements DbSpecificDriver {
    private $mConnectionId = NULL;

    /**
     * @brief Returns supported database systems.
     * @return String "mysql" which is only database system
     * supported by this driver.
     */
    public static function getSupportedDbs() {
        return "mysql";
    }

    public function createSpecificQuery($queryType, $tableName, $tableAlias, DbDriver $dbDriver, $options = array()) {
        switch($queryType) {
            case DbQuery::SELECT_QUERY:
                return new MySqlSelectQuery($tableName, $tableAlias, $dbDriver);
            case DbQuery::INSERT_QUERY:
                return new MySqlInsertQuery($tableName, $dbDriver);
            case DbQuery::UPDATE_QUERY:
                return new MySqlUpdateQuery($tableName, $dbDriver);
            case DbQuery::DELETE_QUERY:
                return new MySqlDeleteQuery($tableName, $dbDriver);
            case DbQuery::TRUNCATE_QUERY:
                return new MySqlTruncateQuery($tableName, $dbDriver);
            case DbQuery::CREATE_QUERY:
                return new MySqlCreateQuery($tableName, $dbDriver);
            case DbQuery::DROP_QUERY:
                return new MySqlDropQuery($options["name"], $options["type"], $dbDriver);
        }
    }

    public function createSpecificCondition($binOp) {
        if($binOp == "AND") return new MySqlCondition("AND");
        return new MySqlCondition("OR");
    }

    public function createSpecificDataType($typeId) {
        return new MySqlDataType($typeId);
    }

    public function connect($host, $username = "", $password = "", $driverArgs = array()) {
        $this->mConnectionId = mysql_connect($host, $username, $password);
        if($this->mConnectionId == false) {
            throw new DbDriverException( I18n::tr("MySQL connection error: @s", array("@s" => mysql_error())) );
        }
    }

    public function disconnect() {
        mysql_close($this->mConnectionId);
    }

    public function selectDatabase($name) {
        if( mysql_select_db($name, $this->mConnectionId) == false ) {
            throw new DbDriverException(
                I18n::tr("Cannot select database '@db'.", array("@db" => $name))
            );
        }
    }

    public function query($query) {
        $res = mysql_query($query, $this->mConnectionId);
        if($res === false) {
            throw new DbDriverException(
                I18n::tr("MySQL query execution error: @s", array("@s" => mysql_error())),
                $query
            );
        }
        else if($res === true) {
            return true;
        }
        // return query result in DbQueryResult object (actually in its implementation)
        return new MySqlQueryResult($res);
    }

}

/**
 * @brief Manipulate MySql results.
 *
 * This class is implementation of \\cfd\\core\\DbQueryResult for
 * MySql specific driver.
 *
 * @see \\cfd\\core\\DbQueryResult, \\cfd\\core\\MySqlSpecificDriver
 */
class MySqlQueryResult implements DbQueryResult {
    private $mQueryResult = NULL;

    /**
     * @brief Creates new object.
     *
     * Creates new object that manipulate with MySQL query result.
     *
     * @param resource $queryResult Resource returned by low-level MySQL database
     * system function.
     */
    public function __construct($queryResult) {
        $this->mQueryResult = $queryResult;
    }

    /**
     * @brief Destroys object.
     *
     * Destructor frees resource which points to result data.
     */
    public function __destruct() {
        if( !is_null($this->mQueryResult) ) mysql_free_result($this->mQueryResult);
    }

    public function fetchRow($type = self::NAME_INDEXES) {
        return mysql_fetch_array($this->mQueryResult,
            $type == self::NUM_INDEXES ? MYSQL_NUM : ($type == self::NAME_INDEXES ? MYSQL_ASSOC : MYSQL_BOTH));
    }

    public function getRowsCount() {
        return mysql_num_rows($this->mQueryResult);
    }

    public function getColumnsCount() {
        return mysql_num_fields($this->mQueryResult);
    }

}

/**
 * @brief MySql's condition.
 *
 * This is class for contitions for MySql database system.
 */
class MySqlCondition extends DbCondition {

    public function compile() {
        $res = "";

        // count of children and children array
        $childrenArr =& $this->getChildren();
        $childrenArrSize = count($childrenArr);

        // adding current props
        if( !empty($this->mLOperand) && !empty($this->mROperand) ) {
            if($childrenArrSize > 0) $res .= "(";
            $res .= $this->mLOperand;
            $res .= " " . $this->mOperator . " ";
            $res .= $this->mROperand;
            if($childrenArrSize > 0) {
                $res .= ")";
                $res .= " " . $this->mBinOperator . " ";
            }
        }

        // adding props of children
        $done = 0;
        foreach($childrenArr as $child) {
            $res .= "(" . $child->compile() . ")";
            if(++$done != $childrenArrSize)  $res .= " " . $this->mBinOperator . " ";
        }
        return $res;
    }

}

/**
 * @brief MySql's data type.
 *
 * Specific class for MySql db driver.
 */
class MySqlDataType extends DbDataType {

    private function getRealDataProperty($propName) {
        static $props = array(
            DbDataType::INTEGER_8    => array("name" => "TINYINT", "us" => true),
            DbDataType::INTEGER_16   => array("name" => "SMALLINT", "us" => true),
            DbDataType::INTEGER_24   => array("name" => "MEDIUMINT", "us" => true),
            DbDataType::INTEGER_32   => array("name" => "INT", "us" => true),
            DbDataType::INTEGER_64   => array("name" => "BIGINT", "us" => true),
            DbDataType::FLOAT_32     => array("name" => "FLOAT", "fl" => true),
            DbDataType::FLOAT_64     => array("name" => "DOUBLE", "fl" => true),
            DbDataType::DECIMAL      => array("name" => "DECIMAL", "fl" => true),
            DbDataType::TEXT_8       => array("name" => "TINYTEXT"),
            DbDataType::TEXT_16      => array("name" => "TEXT"),
            DbDataType::TEXT_24      => array("name" => "MEDIUMTEXT"),
            DbDataType::TEXT_32      => array("name" => "LONGTEXT"),
            DbDataType::BLOB_8       => array("name" => "BLOB"),
            DbDataType::BLOB_16      => array("name" => "BLOB"),
            DbDataType::BLOB_24      => array("name" => "MEDIUMBLOB"),
            DbDataType::BLOB_32      => array("name" => "LONGBLOB"),
            DbDataType::CHAR         => array("name" => "CHAR"),
            DbDataType::VARCHAR      => array("name" => "VARCHAR"),
            DbDataType::ENUM         => array("name" => "ENUM", "set" => true),
            DbDataType::SET          => array("name" => "SET", "set" => true),
            DbDataType::DATE         => array("name" => "DATE"),
            DbDataType::TIME         => array("name" => "TIME"),
            DbDataType::DATETIME     => array("name" => "DATETIME"),
            DbDataType::TIMESTAMP    => array("name" => "TIMESTAMP")
        );
        return !empty($props[$this->getType()][$propName]) ? $props[$this->getType()][$propName] : false;
    }

    public function compile() {
        // note that we use !col instead of real col name
        $colName = "!col";
        $res = $colName . " " . $this->getRealDataProperty("name") . " ";

        // adding size and scale info
        if( $this->getSize() != 0 && !$this->getRealDataProperty("set") ) {
            $res .= "(" . $this->getSize();
            if( $this->getScale() != 0 && $this->getRealDataProperty("fl") ) {
                $res .= ", " . $this->getScale();
            }
            $res .= ") ";
        }
        else if( $this->getRealDataProperty("set") ) {
            $res .= "(" . implode( ",", $this->getSetArray() ) . ") ";
        }

        // adding info that are on same line (statement)
        if( !is_null( $this->getDefaulVal() ) ) $res .= "DEFAULT " . $this->getDefaulVal() . " ";
        if( $this->isUnsigned() && $this->getRealDataProperty("us") ) $res .= "UNSIGNED ";
        if( !$this->isNullable() ) $res .= "NOT NULL ";
        if( $this->isIncrement() ) $res .= "AUTO_INCREMENT ";

        return $res;
    }

}

/**
 * @brief MySql's select query.
 *
 * Implementation of \\cfd\\core\\DbSelectQuery specific for
 * MySql database system.
 *
 * @see \\cfd\\core\\DbSelectQuery
 */
class MySqlSelectQuery extends DbSelectQuery {

    public function compile() {
        // creating query for MySQL
        $res = "SELECT ";

        // if we want only distinct entries
        if( $this->isOnlyDistinct() ) $res .= "DISTINCT ";

        // right table name - alias if specified or full name if not
        $tableName = is_null( $this->getTableNameAlias() ) ? $this->getTableName() : $this->getTableNameAlias();

        // adding columns names and alises
        $colArr = $this->getAllColumns();
        $colArrSize = count($colArr);

        $done = 0;
        foreach($colArr as &$col) {
            $res .= $col["name"];
            if( !empty($col["alias"]) ) $res .= " AS " . $col["alias"];
            if(++$done != $colArrSize) $res .= ", ";
        }

        // adding expressions with there aliases
        $expArr = $this->getAllExpressions();
        $expArrSize = count($expArr);
        if($expArrSize > 0) $res .= ", ";

        $done = 0;
        foreach($expArr as &$exp) {
            $res .= $exp["expression"] . " AS " . $exp["alias"];
            if(++$done != $expArrSize) $res .= ", ";
        }

        // adding table name with alias to query
        $res .= " FROM " . $this->getTableName();
        if( !is_null($this->getTableNameAlias()) ) $res .= " AS " . $this->getTableNameAlias();

        // adding joined tables
        $joinsArr = $this->getAllJoins();
        foreach($joinsArr as &$join) {
            switch($join["type"]) {
                case DbSelectQuery::INNER_JOIN:
                    $res .= " INNER JOIN ";
                    break;
                case DbSelectQuery::LEFT_JOIN:
                    $res .= " LEFT JOIN ";
                    break;
                case DbSelectQuery::RIGHT_JOIN:
                    $res .= " RIGHT JOIN ";
                    break;
                case DbSelectQuery::FULL_JOIN:
                    $res .= " FULL JOIN ";
                    break;
            }
            $query = $join["query"];
            $on = $join["on"];
            $res .= $query->getTableName();
            if( !is_null($query->getTableNameAlias()) ) $res .= " AS " . $query->getTableNameAlias();
            if( !$on->isEmpty() ) $res .= " ON " . $on->compile();
        }

        // adding where clause
        $cond = $this->getAllConditions();
        if( !$cond->isEmpty() ) {
            $res .= " WHERE " . $cond->compile();
        }

        // adding "order by" clause
        if( !empty($this->mOrdering) ) {
            $res .= " ORDER BY ";
            $done = 0;
            $size = count($this->mOrdering);
            foreach($this->mOrdering as $col) {
                $res .= $col["column"] . " ";
                if($col["type"] == DbSelectQuery::DESC_ORDER) {
                    $res .= "DESC";
                }
                else {
                    $res .= "ASC";
                }
                if(++$done != $size) $res .= ", ";
            }
        }

        // adding limit of selection
        if($this->mLimitCount != 0 || $this->mLimitFrom != 0) {
            $res .= " LIMIT " . $this->mLimitFrom . ", " . $this->mLimitCount;
        }

        return $res;
    }

}

/**
 * @brief MySql's insert query.
 *
 * Implementation of \\cfd\\core\\DbInsertQuery specific for
 * MySql database system.
 *
 * @see \\cfd\\core\\DbInsertQuery
 */
class MySqlInsertQuery extends DbInsertQuery {

    public function compile() {
        // creating strings with column names and values
        $columns = "";
        $values = "";
        $sizeOfValuesArr = count($this->mValues);
        $done = 0;
        foreach($this->mValues as &$val) {
            $columns .= $val["column"];
            $values .= $val["value"];
            if(++$done != $sizeOfValuesArr) {
                $columns .= ", ";
                $values .= ", ";
            }
        }
        return ("INSERT INTO " . $this->getTableName() . "(" . $columns . ") VALUES(" . $values . ")");
    }

}

/**
 * @brief MySql's update query.
 *
 * Implementation of \\cfd\\core\\DbUpdateQuery specific for
 * MySql database system.
 *
 * @see \\cfd\\core\\DbUpdateQuery
 */
class MySqlUpdateQuery extends DbUpdateQuery {

    public function compile() {
        // creating strings with column names and its new values
        $columnsNewValues = "";
        $sizeOfValuesArr = count($this->mNewValues);
        $done = 0;
        foreach($this->mNewValues as &$val) {
            $columnsNewValues .= $val["column"] . "=" . $val["value"];
            if(++$done != $sizeOfValuesArr) {
                $columnsNewValues .= ", ";
            }
        }

        // building final string
        $res = "UPDATE " . $this->getTableName() . " SET " . $columnsNewValues;
        if( !$this->mCondition->isEmpty() ) $res .= " WHERE " . $this->mCondition->compile();
        return $res;
    }

}

/**
 * @brief MySql's delete query.
 *
 * Implementation of \\cfd\\core\\DbDeleteQuery specific for
 * MySql database system.
 *
 * @see \\cfd\\core\\DbDeleteQuery
 */
class MySqlDeleteQuery extends DbDeleteQuery {

    public function compile() {
        // this is really easy... really!
        $res = "DELETE FROM " . $this->getTableName();
        if( !$this->mCondition->isEmpty() ) $res .= " WHERE " . $this->mCondition->compile();
        return $res;
    }

}

/**
 * @brief MySql's truncate query.
 *
 * Implementation of \\cfd\\core\\DbTruncateQuery specific for
 * MySql database system.
 *
 * @see \\cfd\\core\\DbTruncateQuery
 */
class MySqlTruncateQuery extends DbTruncateQuery {

    public function compile() {
        // easy as a slape (I hope it's possible to say it in english :D)
        return "TRUNCATE TABLE " . $this->getTableName();
    }

}

/**
 * @brief MySql's create query.
 *
 * Implementation of \\cfd\\core\\DbCreateQuery specific for
 * MySql database system.
 *
 * @see \\cfd\\core\\DbCreateQuery
 */
class MySqlCreateQuery extends DbCreateQuery {

    public function compile() {
        $res = "CREATE TABLE ";
        if( $this->mIfNotExists ) $res .= "IF NOT EXISTS ";
        $res .= $this->getTableName() . "(";

        // adding columns list with data types definitions
        $sizeOfColumnsArr = count($this->mColumns);
        $done = 0;
        foreach($this->mColumns as $colName => $colType) {
            $res .= DbDriver::substituteVariables( $colType->compile(), array("!col" => $colName) );
            if(++$done != $sizeOfColumnsArr) $res .= ", ";
        }

        // adding primary key info if any
        if( !is_null($this->mPrimaryKeyColumn) ) $res .= ", PRIMARY KEY(" . $this->mPrimaryKeyColumn . ")";

        // adding foreign keys info if any
        if( !empty($this->mForeignKeys) ) {
            foreach($this->mForeignKeys as $colName => &$keyInfo) {
                $res .= ", ";
                if( !empty($keyInfo["name"]) ) $res .= "CONSTRAINT " . $keyInfo["name"] . " ";
                $res .= "FOREIGN KEY(" . $colName . ") REFERENCES " . $keyInfo["table"] . "(" . $keyInfo["column"] . ")";
            }
        }

        // adding unique keys info if any
        if( !empty($this->mUniqueKeys) ) {
            foreach($this->mUniqueKeys as $colName => &$keyInfo) {
                $res .= ", ";
                if( !empty($keyInfo["name"]) ) $res .= "CONSTRAINT " . $keyInfo["name"] . " ";
                $res .= "UNIQUE(" . $colName . ")";
            }
        }

        $res .= ")";
        return $res;
    }

}

/**
 * @brief MySql's drop query.
 *
 * Implementation of \\cfd\\core\\DbDropQuery specific for
 * MySql database system.
 *
 * @see \\cfd\\core\\DbDropQuery
 */
class MySqlDropQuery extends DbDropQuery {

    public function compile() {
        $res = "DROP ";
        if( $this->mType == DbDropQuery::DROP_DATABASE ) $res .= "DATABASE";
        else $res .= "TABLE";
        return $res . " " . $this->mName;
    }

}
