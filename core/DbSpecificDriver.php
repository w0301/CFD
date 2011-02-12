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
 * system specific drivers.
 *
 * @see \\cfd\\core\\DbDriver, \\cfd\\core\\DbQuery
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
     * @brief Returns new query object.
     *
     * This function creates new object of type that extends
     * \\cfd\\core\\DbDriver. Created object has to be able to
     * operate query of $queryType type. It also has to send its
     * query to $tableName table.
     *
     * @param integer $queryType Query type for which object will be created. Has to be
     * one of following constant:
     * @code
     *  \cfd\core\DbQuery::SELECT_QUERY
     *  \cfd\core\DbQuery::INSERT_QUERY
     *  \cfd\core\DbQuery::UPDATE_QUERY
     *  \cfd\core\DbQuery::DELETE_QUERY
     *  \cfd\core\DbQuery::CREATE_QUERY
     *  \cfd\core\DbQuery::TRUNCATE_QUERY
     *  \cfd\core\DbQuery::ALTER_QUERY
     *  \cfd\core\DbQuery::DROP_QUERY
     * @endcode
     * @param string $tableName Name of table that will be affected by returned query.
     * @param string $tableAlias Alias for table.
     * @param object $dbDriver Database driver object that create this query and is suppose
     * to send it.
     * @param array $options Additional options passed to query object during creation.
     * These options are query and database system specific.
     * @return New query object suitalbe to handle $queryType query.
     */
    public static function createSpecificQuery($queryType, $tableName, $tableAlias, DbDriver $dbDriver, $options = array());

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

}
