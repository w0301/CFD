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

/**
 * @brief Base class for all CFD exceptions.
 *
 * This class is used as base class for all other exceptions
 * classes in CFD platform.
 */
class Exception extends \Exception {
    /**
     * Constructs new exception object.
     *
     * @param string $msg Message of exception that describes it.
     * @param int $code Code of exception, unique for every single exception type.
     * @param Exception $prev Previous exception. It's used only when throwing from catch block.
     */
    public function __construct($msg = "", $code = 0, \Exception $prev = NULL) {
        parent::__construct($msg, $code, $prev);
    }

}
