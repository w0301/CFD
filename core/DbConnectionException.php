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
 * @brief Indicates problem during connecting to database.
 *
 * This exception is thrown by DbConnection's constructor to
 * indicate that something went wrong during connecting to database.
 *
 * @see \\cfd\\core\\DbConnection
 */
class DbConnectionException extends Exception {
    /**
     * Code returned by getCode() function for this exception.
     */
    const CODE = 4;
    private $mConnectionObj = NULL;

    /**
     * Constructs new exception object.
     *
     * @param string $msg Message of exception that describes it.
     * @param object $connObj Object that threw this exception. You can use it to
     * return ODP object and more info about the error and connection.
     * @param Exception $prev Previous exception. It's used only when throwing from catch block.
     */
    public function __construct($msg, DbConnection $connObj, \Exception $prev = NULL) {
        parent::__construct($msg, self::CODE, $prev);
        $this->mConnectionObj = $connObj;
    }

    /**
     * @brief Returns connection object.
     *
     * @return Object of type DbConnection that threw this exception.
     * Use this object to return more info about error or connection.
     * @see \\cfd\\core\\DbConnection
     */
    public function getConnection() {
        return $this->mConnectionObj;
    }

}
