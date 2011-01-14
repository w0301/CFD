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
 * @brief Simplest signal class.
 *
 * This class represents signal that simple call
 * all connected functions when it's emited.
 *
 * @see \\cfd\\core\\Signal
 */
class NormalSignal extends Signal {

    /**
     * Constructs new NormalSignal object.
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
     * This function simple calls all connected functions
     * in same order as they were connected. It doesn't define
     * any special rules for function calling.
     *
     * @return Array of return values as described in Signal::emit().
     * This array is empty if there wasn't any connected function.
     */
    public function emit() {
        $params = func_get_args();
        $ret = array();
        foreach($this->mFunctionsList as $key => &$val) {
            $ret[] = array( "func" => $val, "val" => $this->callFunction($val, $params) );
        }
        return $ret;
    }

}
