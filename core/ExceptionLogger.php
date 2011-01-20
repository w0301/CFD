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
 * @brief Functionality for exception loggers.
 *
 * This functionality is implemented by all exception loggers.
 * If you want to implement it in your plugin, your plugin main class
 * has to implement this interface.
 *
 * Note that only \\cfd\\core\\Object subclasses can implement
 * this interface.
 *
 * @see \\cfd\\core\\ExceptionHandling
 */
interface ExceptionLogger extends Functionality {

    /**
     * @brief Function that logs exceptions.
     *
     * This function is connected to exception logging signal by
     * \\cfd\\core\\ExceptionHandling::addExceptionLogger() function.
     * It should log exception for further examination of errors.
     *
     * @param string $msg Message that should be logged by logger.
     * If this is empty string ("") exception handler should make own
     * message (for example by looking to $exc's properties).
     * @param object $exc Exception that was caught by catch block that called this
     * function.
     */
    public function logException($msg, \Exception $exc);

}