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

/**
 * @brief Holds main settings for CFD platform.
 *
 * This class
 */
class MainSettings {
    /**
     * This variable specifies in which directory core
     * classes reside. This property is adjusted automatically
     * and can't be adjusted manually.
     */
    private static $sCoreDirectory;

    /**
     * This variable holds path to root of CFD platform.
     */
    private static $sRootDirectory;

    /**
     * @brief Static constructor.
     *
     * This function is called automatically. User mustn't
     * call it. This function sets main settings and does
     * some initial setup.
     */
    public static function __static() {
        // setting up directories
        self::$sRootDirectory = dirname(__FILE__);
        self::$sCoreDirectory = self::$sRootDirectory . "/core";
        if( !is_dir(self::$sCoreDirectory) ) {
            die("Low level fatal error: Directory with core files and classes does not exist: " . self::$sCoreDirectory);
        }

        // overriding PHP's function for autoloading classes
        require_once(self::getCoreDirectoryPath() . "/ClassAutoloading.php");

        function __autoload($className) {
            \cfd\core\ClassAutoloading::autoload($className);
        }
    }

    /**
     * @return Absolute path to directory where core files and classes reside.
     * There is not trailling "/" at the end of returned string.
     */
    public static function getCoreDirectoryPath() {
        return self::$sCoreDirectory;
    }

    /**
     * @return Absolute path to root directory of CFD installation.
     * There is not trailling "/" at the end of returned string.
     */
    public static function getRootDirectoryPath() {
        return self::$sRootDirectory;
    }

} MainSettings::__static();
