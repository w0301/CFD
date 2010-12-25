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

require_once("Signal.php");

/**
 * @brief Reverse calling signal with condition.
 *
 * This signal calls connected function only if previously called
 * function report that it should keep calling. This reporting is
 * done by reference which is sent as first argument by signal.
 * Each signal of this class send reference variable as first parameter.
 * This reference points to variable that holds value @b true. If this variable
 * is @b true after function return signal will not call any other function. So
 * if you want to call early connected functions set this variable to false in your
 * connected function (for example if function fails to do its job). This type of signal
 * is used at places that has some default behaviour for doing things and use can replace
 * this behaviour by his own (for example strings translations - default is not to translate
 * them but modules can add function that translates them using database's list of translations,
 * but if database lookup fails function does not set reference to @b true and default behaviour
 * will be done by previously connected function). Prototype of function that is connected to this
 * signal has to look like this:
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
     * 
     * @param object $parent Parent of new object.
     */
    public function __construct(Object $parent = NULL) {
        parent::__construct($parent);
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
     * this argument to @b false if signal should continue calling functions.
     * If function does not set this reference variable to @b false signal
     * will continue calling early connected functions but before call it sets
     * reference variable to @b true again.
     *
     * @return @b Return @b value of first called function that doesn't set reference
     * variable to false. Or @b false if all called functions set reference
     * variable to false (use '===' to test return value).
     */
    public function emit() {
        $stopCalling = true;
        $params = array(0 => &$stopCalling);
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
            $params[0] = true;
            $lastRet = self::callFunction($val, $params);
            if($params[0] === true) {
                return $lastRet;
            }
        }
        return false;
    }

}
