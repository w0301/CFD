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

// in this file there are *MySqlQuery classes which are private
// and can be created only by DbSpecificDriver::createSpecificQuery() function

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

    public static function createSpecificQuery($queryType, $tableName, $options = array()) {

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
