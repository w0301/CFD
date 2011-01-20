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

require_once("ConditionalSignal.php");
require_once("DefaultClassLoader.php");

/**
 * @brief Owner of ClassLoader functionality.
 *
 * This class owns ClassLoader functionality. That
 * means that this class provides static functions for
 * adding class loaders. Note that there is one default
 * class loader added from the begining (use
 * \\cfd\\core\\DefaultClassLoader::getLoader() function to get it).
 *
 * @see \\cfd\\core\\ClassLoader, \\cfd\\core\\DefaultClassLoader
 */
class ClassAutoloading {
	/**
     * @brief Signal that loads classes.
     *
     * This signal is emitted when class is about to be
     * autoloaded - when it's not found and user wants to use it.
     * This signal send only one argument and it's full class name
     * that has to be loaded (with namespace name as prefix).
     * This signal is of \\cfd\\core\\ConditionalSignal type so
     * your connected functions has to follow rules descriped in
     * its documentation (read it!). As default DefaultClassLoader's function
     * loadClass() is connected to this signal (there is global instance
     * of DefaultClassLoader class - see \\cfd\\core\\DefaultClassLoader::getLoader()).
     *
     * Prototype example for signal:
     * @code
     * 	function func($fullClassName, &$succeed);
     * @endcode
     *
     * @see \\cfd\\core\\DefaultClassLoader
     */
    protected static $sLoadClass;

    /**
     * @brief Static initializer.
     *
     * Never call this function. It's called automatically.
     */
    public static function __static() {
        static $called = false;
        if($called === true) return;
        $called = true;

        // creating signal
        self::$sLoadClass = new ConditionalSignal();

        // adding default loader
        self::addClassLoader( DefaultClassLoader::getLoader() );
    }

    /**
     * @brief Adds new class loader.
     *
     * This simply connects loader's function loadClass()
     * to $sLoadClass signal. You will probably never need to
     * call this function.
     *
     * @param ClassLoader $loader Loader object that's function will be addded.
     */
    public static function addClassLoader(ClassLoader $loader) {
        self::$sLoadClass->connect( array($loader, "loadClass") );
    }

    /**
     * @brief Removes class loader.
     *
     * This simply disconnects loader's function loadClass()
     * from $sLoadClass signal. You will probably never need to
     * call this function.
     *
     * @param ClassLoader $loader Loader object that's function will be removed.
     */
    public static function removeClassLoader(ClassLoader $loader) {
        self::$sLoadClass->disconnect( array($loader, "loadClass") );
    }

    /**
     * @brief Performs class autoloading process.
     *
     * This function is called by PHP when class autoloading
     * is needed (when user use class that was not yet loaded).
     *
     * @param string $className Name of class that was desired.
     * @throws ClassNotFoundException Thrown when desired class was not found.
     */
    public static function autoload($className) {
        self::$sLoadClass->emit($className);
        if( !self::$sLoadClass->wasLastEmitSuccessful() ) {
            $classNameSize = strlen($className);
            $lastNsSeparatorI = strrpos($className, "\\");
            $namespaceName = substr($className, 0, $lastNsSeparatorI);
            $className = substr($className, $lastNsSeparatorI + 1, $classNameSize - $lastNsSeparatorI - 1);
            throw new ClassNotFoundException(
                    I18n::tr("Desired class was not found and can not be loaded."),
                    $namespaceName, $className
                    );
            }
        }

} ClassAutoloading::__static();
