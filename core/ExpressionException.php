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
     * Code returned by getCode() function for this exception.
     */
    const CODE = 4;
    private $mExpObj = NULL;

    /**
     * Constructs new exception object.
     *
     * @param string $msg Message of exception that describes it.
     * @param object $expObj Object that threw this exception. You can use it to
     * return expression string and variables.
     * @param Exception $prev Previous exception. It's used only when throwing from catch block.
     */
    public function __construct($msg, ExpressionEvaluator $expObj, \Exception $prev = NULL) {
        $mExpObj = $expObj;
        parent::__construct($msg, self::CODE, $prev);
    }

    /**
     * @brief Returns expression.
     *
     * @return Object of type ExpressionEvaluator that threw this
     * exception. Use this object to return expression string and/or
     * defined variables.
     * @see \\cfd\\core\\ExpressionEvaluator
     */
    public function getExpression() {
        return $mExpObj;
    }
}
