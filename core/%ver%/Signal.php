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
 * @brief Base class for all Signal classes.
 *
 * This class is base class for all other signal classes.
 * Signals are used as connection point for PHP functions.
 * When any signal is emited by emit() function all connected functions
 * are called. They are more signal classes because call rules of connected
 * function can be different. This class provide abstract interface for
 * those signal classes.
 *
 * In CFD classes signals are usually public variables (static or not-static). In
 * documentation for those variables it's strictly told which parameters are sent
 * with signal so it's easy to implement function that can handle such signal.
 *
 * @see \\cfd\\core\\NormalSignal
 */
abstract class Signal {
    /**
     * Array that contains connected functions.
     */
    protected $mFunctionsList = array();

    /**
     * Disconnects all functions from signal.
     * This is destructor so it's called when
     * object is being destroyed.
     */
    public function __destruct() {
         $this->disconnectAll();
    }

    /**
     * @brief Calls function.
     *
     * This function calls function by given name.
     *
     * @param callback $func Name or array for function.
     * @param array $params Parameters that will be passed to function.
     * Parameters have to be array's values.
     */
    protected static function callFunction($func, $params) {
        return call_user_func_array($func, $params);
    }

    /**
     * @brief Connects function to signal.
     *
     * Connected function will be call during emit() function
     * execution acording to current singal's rules.
     *
     * @param callback $func Function name in string or array
     * with object on 0. index a function name on 1. index.
     * @see diconnect(), emit()
     */
    public function connect($func) {
        $this->mFunctionsList[] = $func;
    }

    /**
     * @brief Disconnects function from signal.
     *
     * Disconnected function won't be call by emit() anymore. Function
     * is disconnected only if it was connected before by connect() function.
     *
     * @param callback $func Same parameter that was passed to connect
     * function before.
     * @see connect(), emit()
     */
    public function disconnect($func) {
        $key = 0;
        if( ($key = array_search($func, $this->mFunctionsList)) !== false ) {
            unset($this->mFunctionsList[$key]);
        }
    }

    /**
     * @brief Disconnects all functions.
     *
     * This function symple disconnect all previously connected
     * functions from this signal.
     *
     * @see connect(), disconnect()
     */
    public function disconnectAll() {
        $this->mFunctionsList = array();
    }

    /**
     * @brief Disconnects all functions with give object.
     *
     * This function disconnects all functions that were
     * connected as function of given object. This function
     * has to be call for an object if you want to destroy
     * the object that has connected functions to any signal.
     *
     * @param object $obj Object which functions will be disconnected.
     */
    public function disconnectAllFrom($obj) {
        foreach($this->mFunctionsList as $key => &$val) {
            if(is_array($val) && $val[0] == $obj) {
                unset($this->mFunctionsList[$key]);
            }
        }
    }

    /**
     * @return Copy of array that holds all connected functions.
     */
    public function getConnectedFunctions() {
        return $this->mFunctionsList;
    }

    /**
     * @brief Calls connected functions.
     *
     * This function is abstract. It has to be implemented
     * in derivative classes. This function do not have to
     * call all connected functions. Calling order and count
     * of called functions is defined by derivative signal class.
     *
     * @param ... Parameters that will be passed to called functions.
     * @return Any implementation of emit() function should return return values
     * of functions in array such that structure of array looks like this:
     * \code
     * array(
     *  0 => array($funcName, $funcNameRetVal),
     *  1 => array($funcName2, $funcName2RetVal),
     *  ...
     * );
     * \endcode
     * Note that $funcNameN can also be array that contains object and
     * its function.
     * @see connect()
     */
    public abstract function emit();

}
