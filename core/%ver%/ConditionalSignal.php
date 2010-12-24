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
 * @brief Reverse calling signal with condition.
 *
 * This signal calls connected function only if previously called
 * function report that it should keep calling. This reporting is
 * done by reference which is sent as first argument by signal.
 * Each signal of this class send reference variable as first parameter.
 * If you want that function that you connected to be last that is called
 * you have to set reference to @b true (last connected function is called first in this signal).
 * This type of signal is used at places that has some default behaviour for
 * doing things and use can replace this behaviour by his own (for example strings
 * translations - default is not to translate them but modules can add function that
 * translates them using database's list of translations, but if database lookup fails function
 * does not set reference to @b true and default behaviour will be done by previously connected function).
 * Prototype of function that is connected to this signal has to look like this:
 * @code
 *  function func(&$stopAfterThis, ...);   // replace '...' by any other arguments
 *                                         // that are signal's object depended
 * @endcode
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
     * This emit() function calls connected functions.
     * Firstly it calls last connected function. This function
     * has to get reference argument as its first argument and set
     * this argument to @b true if signal should stop calling functions.
     * If function does not set this reference variable to @true signal
     * will call last but one connected function.
     *
     * @return Return value of function that set reference variable to @b true,
     * @b false when there was not such function connected (use '===' to test!).
     * Note that only one value is returned and this value is not in array (this
     * behaviour is different from one that is described in Signal::emit()).
     */
    public function emit() {
        $stopCalling = false;
        $params = array(0 => &$$stopCalling);
        $emit_params = func_get_args();
        foreach($emit_params as &$val) {
            $params[] = $val;
        }

        $val = end($this->mFunctionsList);
        $lastRet = self::callFunction($val, $params);
        if($params[0] === true) {
            return $lastRet;
        }
        while( ($val = prev($this->mFunctionsList)) !== false ) {
            $lastRet = self::callFunction($val, $params);
            if($params[0] === true) {
                return $lastRet;
            }
        }
        return false;
    }

}
