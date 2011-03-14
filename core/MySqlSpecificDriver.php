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

    public static function createSpecificQuery($queryType, $tableName, $tableAlias, DbDriver $dbDriver, $options = array()) {
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
            case DbQuery::DROP_QUERY:
                return new MySqlDropQuery($options["name"], $options["type"], $dbDriver);
        }
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
 * @brief Manipulate MySQL results.
 *
 * This class is implementation of \\cfd\\core\\DbQueryResult for
 * MySQL specific driver.
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
        if( $this->getType() == DbDropQuery::DATABASE_DROP ) $res .= "DATABASE";
        else $res .= "TABLE";
        return $res . " " . $this->getName();
    }

}
