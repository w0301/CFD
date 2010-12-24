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
 * @brief Signal that calls function only if condition is met.
 *
 * This signal calls connected function only if given condition
 * is met. Condition is tested before each calling and if it's
 * true call is performed otherwise calling is stopped and emit
 * function returns. For now condition is: "%lastreturn% !== true"
 * and can not be changed (%lastreturn% is return value of function that
 * was called most recently, before calling first function always false).
 *
 * @see \\cfd\\core\\Signal
 */
class ConditionalSignal extends Signal {

    /**
     * Constructs new ConditionalSignal object.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Destructs object by calling parent's destructor.
     */
    public function __destruct() {
        parent::__destruct();
    }

    /**
     * @brief Calls connected functions.
     *
     * This emit() function calls connected function only
     * if object's condition is met. It also calls functions
     * in reverse order => function that was connected as last
     * is called as first.
     *
     * @return Array of return values of called functions as described in Signal::emit().
     */
    public function emit() {
        $params = func_get_args();
        $ret = array();
        $val = end($this->mFunctionsList);
        $ret[] = array( $val, $lastRet = self::callFunction($val, $params) );
        while( ($val = prev($this->mFunctionsList)) !== false ) {
            if($lastRet !== true) {
                $ret[] = array( $val, $lastRet = self::callFunction($val, $params) );
            }
        }
        return $ret;
    }

}
