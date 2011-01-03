<?php
/*
 * Copyright (C) 2010 Richard Kakaš.
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

require_once("Exception.php");

/**
 * @brief Exception that indicates bad type.
 *
 * This exception is thrown when any variable has
 * bad type. For example when object which is subclass
 * of Object was expected bud object which is subclass of
 * Exception was presented.
 * Code of this exception is @b 2 (use getCode() to get it).
 */
class BadTypeException extends Exception {

    /**
     * Constructs new object.
     *
     * @param string $msg Message that descibes exception.
     * @param object $prev Previously thrown exception.
     */
    public function __construct($msg, \Exception $prev = NULL) {
        parent::__construct($msg, 2, $prev);
    }

}
