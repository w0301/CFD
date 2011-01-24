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
 * @brief Indicates problem during database queries.
 *
 * This exception is thrown by database system specific drivers
 * to indicate problem during query execution or connection creation.
 *
 * @see \\cfd\\core\\DbDriver, \\cfd\\core\\DbSpecificDriver
 */
class DbDriverException extends Exception {
    /**
     * Code returned by getCode() function for this exception.
     */
    const CODE = 5;
    private $mQuerySent = "";

    /**
     * Constructs new exception object.
     *
     * @param string $msg Message of exception that describes it.
     * @param string $query Query that was sent before this exception occured.
     * Set to "" if exception was not thrown because of query error.
     * @param Exception $prev Previous exception. It's used only when throwing from catch block.
     */
    public function __construct($msg, $query = "", \Exception $prev = NULL) {
        parent::__construct($msg, self::CODE, $prev);
        $this->mQuerySent = $query;
    }

    /**
     * @brief Returns query that raised exception.
     *
     * @return @b String that contains query that raised this exception. This
     * string is empty ("") if exception was not raised because of query fail.
     */
    public function getQuery() {
        return $this->mQuerySent;
    }

}
