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
 * @brief Used as default exception handler.
 *
 * This class describes default exception handler. Its object is connected
 * by \\cfd\\core\\ExceptionHandling::addExceptionHandler() function during
 * \\cfd\\core\\ExceptionHandling's static initialization (use getHandler() function
 * to get this instance of object).
 *
 * @see \\cfd\\core\\ExceptionHandling, \\cfd\\core\\ExceptionHandling::addExceptionHandler()
 */
class DefaultExceptionHandler extends Object implements ExceptionHandler {
    private static $sHandler = NULL;

    /**
     *
     * Static initialization of class. Never call this function!
     */
    public static function __static() {
        if( !is_null(self::$sHandler) ) return;

        // creating default handler object
        self::$sHandler = new DefaultExceptionHandler();
    }

    /**
     * @brief Constructs new object.
     *
     * @param object $parent Parent of new object.
     */
    public function __construct(Object $parent = NULL) {
        parent::__construct($parent);
    }

    /**
     * @brief Function for handling.
     *
     * Simply output message and ends script. All '\\n' characters are
     * automatically replaced by "<br/>" by this function. All '\\t' characters
     * are automatically replaced by 4 html spaces. If message is not
     * specified it's tooked from exception's getMessage() function.
     *
     * @param string $msg Message that was constructed in catch block.
     * @param object $exc Exception that was caught in by catch block.
     * @param boolean $succeed Reference that is used to indicate if handling was
     * successful or not.
     * @see \\cfd\\core\\ExceptionHandler
     */
    public function handleException($msg, \Exception $exc, &$succeed) {
        if($msg == "") {
            $msg = $exc->getMessage();
        }
        $msg = str_replace("\n", "<br/>", $msg);
        $msg = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $msg);
        die($msg);
    }

    /**
     * @brief Returns default handler's object.
     *
     * This function returns object that is used as default exception
     * handler (i.e. handler that is connected by \\cfd\\core\\ExceptionHandling::addExceptionHandler()
     * if you want so or not). You can use returned object to adjust default's handler
     * parameters (this might be faster approach than implementing own handler by plugin).
     *
     * @return @b Object of type \\cfd\\core\\DefaultExceptionHandler that is used as
     * default exception handler.
     * @see \\cfd\\core\\ExceptionHandling
     */
    public static function getHandler() {
        return self::$sHandler;
    }

} DefaultExceptionHandler::__static();
