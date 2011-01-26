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
 * @brief Interface for all specific db drivers.
 *
 * This is interface that has to be implemented by all database
 * system drivers. Functions that are listed in this interface add
 * support for translating CFD's queries to database specific query.
 * This translating is needed because not all database systems use same
 * queries. Mainly there are problems with different data types. However
 * if database system is very similar to one that already has specific driver
 * it's good idea to subclass existing driver rather than implementing everything
 * from scratch.
 *
 * @see \\cfd\\core\\DbDriver
 */
interface DbSpecificDriver {

    /**
     * @brief Returns supported database systems.
     *
     * This function returns names of database systems that are
     * supported by the driver. This is good to determine which driver
     * should be used for user specified database system.
     *
     * @return @b String with name of supported database system, or @b array
     * that contains strings each with name of one supported database system.
     */
    public static function getSupportedDbs();

    /**
     * @brief Creates connection to database system.
     *
     * This function creates connection to database system
     * and store it in object's property for further use.
     *
     * @throws DbDriverException When connection failed.
     * @param string $host Hostname of database system. For most database system this
     * is URL address, but for some drivers this can be also path to local file.
     * @param string $username Name of user that will be logged in to database system.
     * @param string $password Password for user.
     * @param array $driverArgs Additional arguments that are driver specific. There should
     * be documentation about these arguments in specific driver class documentation.
     */
    public function connect($host, $username = "", $password = "", $driverArgs = array());

    /**
     * @brief Terminated connection.
     *
     * This function terminates connection that was previously
     * created by connect() function.
     *
     * @see connect()
     */
    public function disconnect();

    /**
     * @brief Selects specific database.
     *
     * This function selects specified database in database system.
     * All queries will be sent to this database after selection is performed.
     *
     * @throws DbDriverException When selection failed - for example when desired
     * database does not exist.
     * @param string $name Name of database that will be selected.
     */
    public function selectDatabase($name);

    /**
     * @brief Sends query to database system.
     *
     * This function sends query @b directly to database system.
     *
     * @throws DbDriverException When query failed to be executed.
     * @param string $query Query that will be send to database system.
     * @return @b Object that is instance of class that implements
     * \\cfd\\core\\DbQueryResult interface, or @b true if query was succesful
     * but it doesn't select any data.
     */
    public function query($query);

    /**
     * @brief Returns string that selects data from table.
     *
     * This function transforms input data to query for database system.
     * For most SQL database systems returned query is in folowing form:
     * @code
     *  SELECT $what FROM $from [WHERE $where]
     * @endcode
     *
     * @param string $what Comma separated list of columns that will be selected.
     * @param string $from Name of table from which will be selected data.
     * @param string $where Condition that is used to determine if row should be selected.
     * @param array $args Array that contains values of all arguments used in string above.
     * Array key is variable name and key's value is variable's value.
     * @param array $orderBy Array containg name of columns that will affect ordering.
     * @param integer $orderType Type of ordering. Set to \\cfd\\core\\DbDriver::ASC_ORDER for
     * ascending ordering or to \\cfd\\core\\DbDriver::DESC_ORDER for descending ordering.
     * @return @b String with select query that is suitable for query() function.
     */
    public function createSelectQuery($what, $from, $where, $args, $orderBy, $orderType);

    /**
     * @brief Returns string that can be used as insert query.
     *
     * This function transforms input arguments to query that is suitable
     * as insert query for database system. For SQL returned query looks like this:
     * @code
     *  INSERT INTO $into($key1, $key2, ...) VALUES($value1, $value2, ...)
     * @endcode
     *
     * @param string $into Name of table to insert into.
     * @param array $values Array where key is a column's name and value
     * is value to be inserted.
     * @param array $args Array that contains values of all variables that are
     * used in columns values and/or in $into string.
     * @return @b String with insert query suitable for query() function.
     */
    public function createInsertQuery($into, $values, $args);

    /**
     * @brief Returns string that can be used as update query.
     *
     * This function transforms input data to query string that can be used
     * for query() function. For SQL returned query looks like this:
     * @code
     *  UPDATE $table SET $newValues WHERE $where
     * @endcode
     *
     * @param string $table Name of table that will be updated.
     * @param array $newValues Array that contains new values. Array's key
     * is column name and key's value is new value for column.
     * @param string $where Where clause of query.
     * @param array $args Array that contains variable names and values of all
     * variables used in $table, $where and $newValues[*] strings.
     * @return @b String with update query string suitable for query() function.
     */
    public function createUpdateQuery($table, $newValues, $where, $args);

    /**
     * @brief Returns string that can be used as delete query.
     *
     * This function transforms input data to query string that can be used for
     * query() function. Fot SQL returned query looks like this:
     * @code
     *  DELETE FROM $from WHERE $where
     * @endcode
     *
     * @param string $from Name of table from which rows will be deleted.
     * @param string $where SQL like condition (where clause) that is used to determine
     * which rows to delete.
     * @param array $args Array that contains variables that will be substituted from
     * $table and $where strings.
     * @return @b String with delete query suitable for query() function.
     */
    public function createDeleteQuery($from, $where, $args);

}
