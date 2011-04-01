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
 * @brief Owner of exception functionalities.
 *
 * This class is owner of ExceptionHandler and ExceptionLogger
 * functionalities. These functionalities has to be implemented
 * if you want to add some steps to exception handling.
 *
 * @see \\cfd\\core\\ExceptionHandler, \\cfd\\core\\ExceptionLogger
 */
class ExceptionHandling {
    /**
     * @brief Signal that handles exception.
     *
     * This signal is emited in handle() function. The handle()
     * function is called in every catch block that wants to propagate
     * exception to CFD's exception handling system.
     *
     * This singla is of \\cfd\\core\\ConditionalSignal type. That means that
     * function connected to this signal has to follow rules defined in ConditionalSignal's
     * documentation. This signal sends two arguments - message that should be display by
     * exception handler and exception that was caught before calling handle() function.
     *
     * Prototype for connected function:
     * @code
     * 	function func($msg, \Exception $exc, &$succeed);
     * @endcode
     *
     * @see \\cfd\\core\\ExceptionHandler, addExceptionHandler(), removeExceptionHandler()
     */
    protected static $sHandleException;

    /**
     * @brief Signal that logs exception.
     *
     * This signal is emited in handle() function. The handle()
     * function is called in every catch block that wants to propagate
     * exception to CFD's exception handling system.
     *
     * This singla is of \\cfd\\core\\NormalSignal type. That means that all
     * function connected to this signal are called during signal's emit.
     * This signal sends two arguments - message that should be logged by logger
     * and exception that was caught before calling handle() function.
     *
     * Prototype for connected function:
     * @code
     * 	function func($msg, \Exception $exc);
     * @endcode
     *
     * @see \\cfd\\core\\ExceptionLogger, addExceptionLogger(), removeExceptionLogger()
     */
    protected static $sLogException;

    /**
     * @brief Static init of class.
     *
     * This function makes some static initialization of class
     * never call it on your own.
     */
    public static function __static() {
        static $called = false;
        if($called === true) return;
        $called = true;

        // creating signals
        self::$sHandleException = new ConditionalSignal();
        self::$sLogException = new NormalSignal();

        // adding default exception handler
        self::addExceptionHandler( DefaultExceptionHandler::getHandler() );
    }

    /**
     * @brief Adds new ExceptionHandler object.
     *
     * Simply connects object to $sHandleException signal.
     *
     * @param object $handler ExceptionHandler object to be added.
     */
    public static function addExceptionHandler(ExceptionHandler $handler) {
        self::$sHandleException->connect( array($handler, "handleException") );
    }

    /**
     * @brief Removes ExceptionHandler object.
     *
     * Simply disconnects object from $sHandleException signal.
     *
     * @param object $handler ExceptionHandler object to be removed.
     */
    public static function removeExceptionHandler(ExceptionHandler $handler) {
        self::$sHandleException->disconnect( array($handler, "handleException") );
    }

    /**
     * @brief Adds new ExceptionLogger object.
     *
     * Simply connects object to $sLogException signal.
     *
     * @param object $logger ExceptionLogger object to be added.
     */
    public static function addExceptionLogger(ExceptionLogger $logger) {
        self::$sLogException->connect( array($logger, "logException") );
    }

    /**
     * @brief Adds new ExceptionLogger object.
     *
     * Simply disconnects object from $sLogException signal.
     *
     * @param object $logger ExceptionLogger object to be removed.
     */
    public static function removeExceptionLogger(ExceptionLogger $logger) {
        self::$sLogException->disconnect( array($logger, "logException") );
    }

    /**
     * @brief Emits signals related to handler.
     *
     * This function should be called in catch block to propagate
     * caught exception to CFD's exception handling system. It emits
     * signals $sLogException and $sHandleException. Handlers that
     * have been added by add functions will receive these signals.
     *
     * Note that $sLogException is emitted as first to allow script
     * termination by $sHandleException signal with appropriate error logged.
     *
     * @param string $msg Message that was formated in catch block. Handler/Logger
     * should use this message to describe error (it can contain '\\n' and '\\t' characters that
     * should be handled by handler/logger). If this is empty string ("") it can
     * format own message by using $exc object.
     * @param object $exc Exception object that was caught by catch block that calls this
     * function.
     */
    public static function handle($msg, \Exception $exc) {
        self::$sLogException->emit($msg, $exc);
        self::$sHandleException->emit($msg, $exc);
    }

} ExceptionHandling::__static();
