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
 * @brief Indicates error in ExpressionEvaluator.
 *
 * This exception is thrown when an error occured during
 * expression evaluation in \\cfd\\core\\ExpressionEvaluator::evaluate()
 * function.
 *
 * @see \\cfd\\core\\ExpressionEvaluator
 */
class ExpressionException extends Exception {
    /**
     * Constructs new exception object.
     *
     * @param string $msg Message of exception that describes it.
     * @param Exception $prev Previous exception. It's used only when throwing from catch block.
     */
    public function __construct($msg, \Exception $prev = NULL) {
        parent::__construct($msg, 3, $prev);
    }
}
