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

    public static function createSpecificQuery($queryType, $tableName, DbDriver $dbDriver, $options = array()) {
        switch($queryType) {
            case DbQuery::SELECT_QUERY:
                return new MySqlSelectQuery($tableName, $dbDriver);
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


    public function createSelectQuery($what, $from, $where, $args, $orderBy, $orderType) {
        // filtering and substituting variables
        if(count($args) > 0) {
            DbDriver::filterVariables($args);
            $what = DbDriver::substituteVariables($what, $args);
            $from = DbDriver::substituteVariables($from, $args);
            $where = DbDriver::substituteVariables($where, $args);
        }

        // creating and returing query for MySQL
        $res = "SELECT " . $what . " FROM " . $from;
        if($where != "") $res .= " WHERE " . $where;
        if( is_array($orderBy) && count($orderBy) > 0 ) {
            $res .= " ORDER BY " . implode(", ", $orderBy);
            $res .= " " . ($orderType == DbDriver::ASC_ORDER ? "ASC" : "DESC");
        }
        return $res;
    }

    public function createInsertQuery($into, $values, $args) {
        // filtering values and substituting them
        if(count($args) > 0) {
            DbDriver::filterVariables($args);
            $into = DbDriver::substituteVariables($into, $args);
            // we are filtering all values!
            foreach($values as &$val) {
                // here we will do quotes because of SQL format
                if( is_string($val) ) $val = "'" . DbDriver::substituteVariables($val, $args) . "'";
            }
        }
        else {
            unset($val);
            // adds quotes to strings
            foreach($values as &$val) {
                if( is_string($val) ) $val = "'" . $val . "'";
            }
        }
        $res = "INSERT INTO " . $into . "(";
        $res .= implode( ",", array_keys($values) );
        $res .= ")" . " VALUES(";
        $res .= implode(",", $values);
        $res .= ")";
        return $res;
    }

    public function createUpdateQuery($table, $newValues, $where, $args) {
        // filtering values and substituting them
        if(count($args) > 0) {
            DbDriver::filterVariables($args);
            $table = DbDriver::substituteVariables($table, $args);
        }
        $res = "UPDATE " . $table . " SET ";
        $size = count($newValues);
        $i = 0;
        foreach($newValues as $key => &$val) {
            // firstly substitute variables
            if( count($args) > 0 && is_string($val) ) $val = DbDriver::substituteVariables($val, $args);

            // and now write to res
            $res .= $key . "=";
            if( is_string($val) ) $res .= "'" . $val . "'";
            else $res .= $val;
            if($i != $size - 1) $res .= ", ";
            $i++;
        }
        $res .= " WHERE " . $where;
        return $res;
    }

    public function createDeleteQuery($from, $where, $args) {
        // filtering and substituting variables
        if(count($args) > 0) {
            DbDriver::filterVariables($args);
            $from = DbDriver::substituteVariables($from, $args);
            $where = DbDriver::substituteVariables($where, $args);
        }

        // creating and returing query for MySQL
        $res = "DELETE FROM " . $from;
        if($where != "") $res .= " WHERE " . $where;
        return $res;
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

        $done = 0;
        $size = count($this->mColumns);
        foreach($this->mColumns as $tableName => &$val) {
            $cols = "";
            if(!$val["all_columns"]) {
                $done2 = 0;
                $size2 = count($val["columns"]);
                foreach($val["columns"] as $colName) {
                    $cols .= $tableName . "." . $colName;
                    if(++$done2 != $size2) $cols .= ", ";
                }
            }
            else {
                $cols = $tableName . "*";
            }
            $res .= $cols;
            if(++$done != $size) $res .= ", ";
        }

        $done = 0;
        $sizeExp = count($this->mExpressions);
        if($size > 0 && $sizeExp > 0) $res .= ", ";
        foreach($this->mExpressions as &$exp) {
            $res .= $exp["expression"] . " AS " . $exp["alias"];
            if(++$done != $size) $res .= ", ";
        }

        $res .= " FROM ";
        $tables =& $this->getTableNames();
        $done = 0;
        $size = count($tables);
        foreach($tables as $tableName) {
            $res .= $tableName;
            if(++$done != $size) $res .= ", ";
        }

        if( !$this->mCondition->isEmpty() ) {
            $res .= " WHERE " . $this->mCondition->compile();
        }

        if($this->mLimitCount != 0 || $this->mLimitFrom != 0) {
            $res .= " LIMIT " . $this->mLimitFrom . ", " . $this->mLimitCount;
        }

        return $res;
    }

}
