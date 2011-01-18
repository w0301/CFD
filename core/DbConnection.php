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
 * @brief Class for creating database connections.
 *
 * This class is used to create objects that are used to connect
 * to databases. Connection is provided by PHP's PDO framework (
 * use getPdo() to get its object). You always have to use PDO's
 * object to send queries to database system. Thanks to PDO CFD
 * platform supports ale database systems that are supported by PDO.
 *
 */
class DbConnection extends Object {
    private $mPdoObject = NULL;

    /**
     * @brief Cretate new connection.
     *
     * This constructor creates new connection to database. The connection is
     * established right after this constructor is called (i.e. after object is
     * created). In most cases you will never have to create your own connection
     * to database because this is handled by CFD by getting connection info from
     * configuration file that was created during CFD's installation.
     *
     * @throws DbConnectionException Thrown when connection to database failed.
     * @param string $dsn String that describes connection informations. See PDO doc
     * for more info about format of this string.
     * @param string $username Name of user that will be logged in to database system.
     * @param string $password Password for user with $username. This password is not
     * needed for some PDO drivers.
     * @param array $pdoDriverOptions Options that will be passed to PDO drivers. See
     * PDO documentation for more info.
     * @param object $parent Parent object of connection.
     */
    public function __construct($dsn, $username = "", $password = "", $pdoDriverOptions = array(), $parent = NULL) {
        parent::__construct($parent);
        try {
            $this->mPdoObject = new \PDO($dsn, $username, $password, $pdoDriverOptions);
        }
        catch(\PDOException $e) {
            throw new DbConnectionException(I18n::tr("Connection to database failed."), $this, $e);
        }
    }

    /**
     * @brief Returns PDO object.
     *
     * @return PDO object that was created during object's creation. You
     * have to use this object to send queries to database.
     */
    public function getPdo() {
        return $this->mPdoObject;
    }

}
