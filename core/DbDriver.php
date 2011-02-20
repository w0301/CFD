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
 * every database system. Functions for direct sending are in special class.
 * This special class is specific for each database system. All this specific
 * classes have to implement \\cfd\\core\\DbSpecificDriver interface.
 *
 * CFD's queries are represented by classes with name: Db*Query where '*'
 * stands for query name (i.e. Insert, Select, Drop etc.). All these classes
 * extends \\cfd\\core\\DbQuery class. Use functions select(), insert() etc.
 * to return instances of these classes (actually instances of private database
 * system depended classes are returned but these classes extends classes mentioned
 * above).
 *
 * @see \\cfd\\core\\DbSpecificDriver, \\cfd\\core\\DbQuery
 */
class DbDriver extends Object {
    private static $sSpecificDrivers = array();
    private $mCurrentDriver = NULL;
    private $mTableNamePrefix = "";

    private function addTablePrefix($table) {
        return $this->mTableNamePrefix . $table;
    }

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
     * @param string $tablePrefix Prefix that will be added to all table names in function like select().
     * @param array $driversOptions Driver specific options in array.
     * @param Object $parent Parent for created object.
     */
    public function __construct($driverName, $host, $dbName = "", $user = "", $pass = "", $tablePrefix = "", $driversOptions = array(), Object $parent = NULL) {
        parent::__construct($parent);

        // init of prefix for table names
        $this->mTableNamePrefix = $tablePrefix;

        // connecting to database system with chosen driver
        if( !array_key_exists($driverName, self::$sSpecificDrivers) ) {
            throw new DbDriverException(
                I18n::tr("Database driver '@driver' does not exist", array("@driver" => @driverName))
            );
        }
        $this->mCurrentDriver = new self::$sSpecificDrivers[$driverName];
        $this->mCurrentDriver->connect($host, $user, $pass, $driversOptions);
        if($dbName != "") $this->selectDatabase($dbName);
    }

    /**
     * @brief Destroys object.
     *
     * Disconnects object from database.
     */
    public function __destruct() {
        parent::__destruct();
        $this->mCurrentDriver->disconnect();
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
     * @brief Returns table prefix.
     *
     * @return @b String that contains prefix for all table names.
     */
    public function getTablePrefix() {
        return $this->mTableNamePrefix;
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
     * @brief Selects database.
     *
     * This function selects database that will receive all queries
     * from this database driver object.
     *
     * @param string $name Name of database.
     * @throws DbDriverException When selection failed.
     */
    public function selectDatabase($name) {
        return $this->mCurrentDriver->selectDatabase($name);
    }

    /**
     * @brief Sends query.
     *
     * This function sends query to database system. Remember that the
     * query string is sent without any processing, that means that it
     * might not be portable and can cause errors for some database systems.
     * Use functions select(), insert() etc. for portable query sending.
     *
     * @throws DbDriverException When query failed to be executed.
     * @param string $str Query string that will be sent to database system.
     * @param array $args Array that contains variables that should be substituted
     * fomr $str.
     * @return @b Object that is instance of \\cfd\\core\\DbQueryResult. Use it
     * to get data, or @b true if query was successful and doesn't select any data.
     */
    public function query($str, $args = array()) {
        DbDriver::filterVariables($args);
        $str = DbDriver::substituteVariables($str, $args);
        return $this->mCurrentDriver->query($str);
    }

    /**
     * @brief Creates select query.
     *
     * Use this function to return select query object that you can
     * edit for your select needs. Returned object extends \\cfd\\core\\DbSelectQuery class.
     *
     * @param string $tableName Name of table that this select query selects from. Note that
     * current table prefix is prepended to this name.
     * @param string $alias Alias used for table. When specified anywhere where table name
     * is needed you have to put this alias in. It's generally good idea to decalre alias
     * because there is table prefix feature which makes it hard to determine exact table name.
     * @return New @b object of type \\cfd\\core\\DbSelectQuery.
     * @see getTablePrefix()
     */
    public function select($tableName, $alias = NULL) {
        return $this->mCurrentDriver->createSpecificQuery(DbQuery::SELECT_QUERY, $this->addTablePrefix($tableName), $alias, $this);
    }

} DbDriver::__static();
