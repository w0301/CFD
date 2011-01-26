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

/**
 * @brief Class that sends queries to database.
 *
 * Instancies of this class are used to send queries to database
 * system. It's provide just general functions that can be used for
 * every database system. Functions for direct sending queries and for
 * translating CFD's query form to database's form are in special class.
 * This special class is specific for each database system. All this specific
 * classes have to implement \\cfd\\core\\DbSpecificDriver interface.
 *
 * CFD's queries are represented by classes with name: Db*Query where '*'
 * stands for query name (i.e. Insert, Select, Drop etc.).
 *
 * @see \\cfd\\core\\DbSpecificDriver
 */
class DbDriver extends Object {
    /**
     * Indicates that ascending ordering should be apply.
     */
    const ASC_ORDER = 1;

    /**
     * Indicates that descending ordering should be apply.
     */
    const DESC_ORDER = 2;

    private static $sSpecificDrivers = array();
    private $mCurrentDriver = NULL;

    /**
     * @brief Filters variables values.
     *
     * Apply filtering to each value in given array. Level of filtering
     * is specified by first character of value's key. This first char
     * has to be one of folowing:
     * @code
     *  @ - if you want to apply htmlspecialchars() filter on variable's value
     *  ! - if you don't want to apply any filter
     * @endcode
     * Array passed to this function can look like this:
     * @code
     *  array("@var1" => "<this will be filtered to avoid 'SQL injections'>",
     *        "!var2" => "this is not filtered => SQL injection is possible!");
     * @endcode
     *
     * @param array $vars Reference to array that's variables will be filtered.
     * @see substituteVariables()
     */
    public static function filterVariables(&$vars) {
        foreach($vars as $key => $val) {
            switch($key[0]) {
                case '@':
                    // apply PHP's htmlspecialchars() function
                    $vars[$key] = htmlspecialchars($val, ENT_QUOTES, "UTF-8");
                    break;
                case '!':
                    // do nothing
                    break;
                default:
                    unset($vars[$key]);
                    break;
            }
        }
    }

    /**
     * @brief Substitude variable names in string.
     *
     * This function should be used after filterVariables() function for
     * each string where variables names should be substituted.
     * Example of substitution:
     * @code
     *  DbDriver::filterVariables($args);
     *  $what = DbDriver::substituteVariables($what, $args);
     *  $from = DbDriver::substituteVariables($from, $args);
     *  $where = DbDriver::substituteVariables($where, $args);
     *  // same for all other input strings for query
     * @endcode
     *
     * @param string $str String in which to look for variable names.
     * @param array $vars Array that was passed to filterVariables() function
     * before calling this function.
     * @see filterVariables()
     */
    public static function substituteVariables($str, $vars) {
        return strtr($str, $vars);
    }

    /**
     * @brief Create new connection to database.
     *
     * Create connection to database server.
     *
     * @throws DbDriverException When connection failed.
     * @param string $driverName Name of driver that will be used. Must one of that is
     * returned by getAllDrivers() function.
     * @param string $host Server to which connect.
     * @param string $dbName Name of database that will be selected from the begining
     * ot empty string ("") when database shouldn't be selected.
     * @param string $user Name of user to login.
     * @param string $pass Password for user.
     * @param array $driversOptions Driver specific options in array.
     * @param Object $parent Parent for created object.
     */
    public function __construct($driverName, $host, $dbName = "", $user = "", $pass = "", $driversOptions = array(), Object $parent = NULL) {
        parent::__construct($parent);

        // connecting to database system with chosen driver
        if( !array_key_exists($driverName, self::$sSpecificDrivers) ) {
            throw new DbDriverException(
                I18n::tr("Database driver '@driver' does not exist", array("@driver" => @driverName))
            );
        }
        $this->mCurrentDriver = new self::$sSpecificDrivers[$driverName];
        $this->mCurrentDriver->connect($host, $user, $pass, $driversOptions);
        if($dbName != "") $this->mCurrentDriver->selectDatabase($dbName);
    }

    /**
     * @brief Static initialization of class.
     *
     * This function is called automatically. Never call it!
     */
    public static function __static() {
        static $called = false;
        if($called) return;
        $called = true;

        // registering all core specific drivers
        self::registerSpecificDriver("\cfd\core\MySqlSpecificDriver");
    }

    /**
     * @brief Registers specific driver.
     *
     * Register specific driver for database system. This registration
     * allows choosing drivers during DbDriver object creation. Registration
     * of all core specific drivers is done during static initialization in
     * __static() function.
     *
     * @param string $name Name of driever's class. This class has to
     * implement \\cfd\\core\\DbSpecificDriver interface.
     */
    public static function registerSpecificDriver($name) {
        if( !( array_key_exists("cfd\core\DbSpecificDriver", class_implements($name)) ) ) {
            throw new BadTypeException(
                I18n::tr('Cannot register specific database driver that does not implement cfd\core\DbSpecificDriver interface.')
            );
        }
        $driversSystems = $name::getSupportedDbs();
        if( !is_array($driversSystems) ) {
            // key is database system name and value is name of its driver class
            self::$sSpecificDrivers[$driversSystems] = $name;
        }
        else {
            // it's array => we have to add all db system names
            foreach($driversSystems as $val) {
                self::$sSpecificDrivers[$val] = $name;
            }
        }
    }

    /**
     * @brief Sends query.
     *
     * This function sends query to database system. Remember that the
     * query string is sent without any processing, that means that it
     * might not be portable and can cause errors for some database systems.
     * Use functions *Query() for portable query sending.
     *
     * @throws DbDriverException When query failed to be executed.
     * @param string $str Query string that will be sent to database system.
     * @return @b Object that is instance of \\cfd\\core\\DbQueryResult. Use it
     * to get data, or @b true if query was successful and doesn't select any data.
     */
    public function query($str) {
        return $this->mCurrentDriver->query($str);
    }

    /**
     * @brief Sends select query.
     *
     * Simply sends query returned by getSelectQuery() function.
     *
     * @throws DbDriverException When query failed to be executed.
     * @return @b Object that is instance of \\cfd\\core\\DbQueryResult.
     * @see getSelectQuery()
     */
    public function selectQuery($what, $from, $where = "", $args = array(), $orderBy = array(), $orderType = self::ASC_ORDER) {
        return $this->query( $this->getSelectQuery($what, $from, $where, $args, $orderBy, $orderType) );
    }


    /**
     * @brief Sends insert query.
     *
     * Simply sends query returned by getSelectQuery() function.
     *
     * @throws DbDriverException When query failed to be executed.
     * @return @b True if inserting was successful, otherwise exception is thrown.
     * Note that @b true is returned if sent query was valid.
     * @see getInsertQuery()
     */
    public function insertQuery($into, $values, $args = array()) {
        return $this->query( $this->getInsertQuery($into, $values, $args) );
    }

    /**
     * @brief Sends update query.
     *
     * Simply sends query returned by getUpdateQuery() function.
     *
     * @throws DbDriverException When query failed to be executed.
     * @return @b True if updating was successful, otherwise exception is thrown.
     * Note that @b true is returned if sent query was valid.
     * @see getUpdateQuery()
     */
    public function updateQuery($table, $newValues, $where, $args = array()) {
        return $this->query( $this->getUpdateQuery($table, $newValues, $where, $args) );
    }

    /**
     * @brief Sends delete query.
     *
     * Simply sends query returned by getDeleteQuery() function.
     *
     * @throws DbDriverException When query failed to be executed.
     * @return @b True if deleting was successful, otherwise exception is thrown.
     * Note that @b true is returned if sent query was valid.
     * @see getDeleteQuery()
     */
    public function deleteQuery($from, $where, $args = array()) {
        return $this->query( $this->getDeleteQuery($from, $where, $args) );
    }

    /**
     * @brief Returns right select query.
     *
     * Calls current driver's createSelectQuery() function.
     *
     * @param string $what Comma separated columns names.
     * @param string $from Table name.
     * @param string $where Where condition.
     * @param array $args Variables (key) and values (value). Format explained
     * in filterVariables() function. Value can be also return of getSelectQuery().
     * @param array $orderBy Array containg name of columns that will affect ordering.
     * Array has to look like this:
     * @code
     *  $arr = array("columnName1", "columnName2");
     * @endcode
     * @param integer $orderType Type of ordering. Set to \\cfd\\core\\DbDriver::ASC_ORDER for
     * ascending ordering or to \\cfd\\core\\DbDriver::DESC_ORDER for descending ordering.
     * @return Select query suitable for query() function.
     * @see \\cfd\\core\\DbSpecificDriver::createSelectQuery(), filterVariables()
     */
    public function getSelectQuery($what, $from, $where = "", $args = array(), $orderBy = array(), $orderType = self::ASC_ORDER) {
        return $this->mCurrentDriver->createSelectQuery($what, $from, $where, $args, $orderBy, $orderType);
    }

    /**
     * @brief Creates insert query.
     *
     * Calls current driver's createInsertQuery() function.
     *
     * @param string $into Name of table to insert into.
     * @param array $values Array with values that will be inserted. Each value
     * has to have key that corresponds to column name.
     * @param array $args Array with variables that will be substituted from $into
     * string and from all string values in $values array (not keys!).
     * @return String that can be used with query() function.
     * @see \\cfd\\core\\DbSpecificDriver::createSelectQuery(), filterVariables()
     */
    public function getInsertQuery($into, $values, $args = array()) {
        return $this->mCurrentDriver->createInsertQuery($into, $values, $args);
    }

    /**
     * @brief Creates update query.
     *
     * Calls current driver's createUpdataQuery() function.
     *
     * @param string $table Name of table that will be updated.
     * @param array $newValues Array with new values. Key in array has to
     * be column's name and array's value has to be new value that will be
     * assigned to column.
     * @param string $where Condition in SQL form that is used to choose which
     * rows will be affected.
     * @param array $args Array with variables that will be substituted from $table,
     * $where and from all $newValues values strings.
     * @return @b String that can be used with query() function. This string contain
     * update query suitable for current database system.
     * @see \\cfd\\core\\DbSpecificDriver::createUpdataQuery(), filterVariables()
     */
    public function getUpdateQuery($table, $newValues, $where, $args = array()) {
        return $this->mCurrentDriver->createUpdateQuery($table, $newValues, $where, $args);
    }

    /**
     * @brief Creates delete query.
     *
     * Calls current driver's createDeleteQuery() function.
     *
     * @param string $from Name of table from which records will be deleted.
     * @param string $where Condition (where clause) which is used to determine
     * which records will be deleted.
     * @param array $args Array with variables that will be substituted from
     * $from and $where arguments.
     * @return @b String that contains created query. This query can be used for
     * query() function.
     * @see \\cfd\\core\\DbSpecificDriver::createDeleteQuery(), filterVariables()
     */
    public function getDeleteQuery($from, $where, $args = array()) {
        return $this->mCurrentDriver->createDeleteQuery($from, $where, $args);
    }

} DbDriver::__static();
