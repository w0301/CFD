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
 * @brief Functionality for exception handlers.
 *
 * This functionality is implemented by all exception handlers.
 * If you want to implement it in your plugin, your plugin main class
 * has to implement this interface.
 *
 * Note that only \\cfd\\core\\Object subclasses can implement
 * this interface.
 *
 * @see \\cfd\\core\\ExceptionHandling
 */
interface ExceptionHandler extends Functionality {

    /**
     * @brief Function that handles exceptions.
     *
     * This function is connected to exception handling signal by
     * \\cfd\\core\\ExceptionHandling::addExceptionHandler() function.
     * It should display exception to user.
     *
     * @param string $msg Message that should be displayed for describing the
     * exception. If this is empty string ("") exception handler should make own
     * message (for example by looking to $exc's properties).
     * @param object $exc Exception that was caught by catch block that called this
     * function.
     * @param boolean $succeed Variable that has to be set to false if exception handling
     * failed. If so early exception handler is called, if not exception handling ends.
     */
    public function handleException($msg, \Exception $exc, &$succeed);

}
