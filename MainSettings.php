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

require_once("VersionChooser.php");

/**
 * @brief Holds main settings for CFD platform.
 *
 * This class is full of static properties. If you want
 * to adjust them you have to manually edit "MainSettings.php"
 * file by assigning default values to class's private properties.
 */
class MainSettings {
	/**
	 * This variable specifies which version of core
	 * classes is being used. To use newest version leave
	 * without default value (this is default). If you want
	 * use specific version assign version string to this property.
	 */
    private static $sCoreVersion;

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
     * This variable holds default paths for ClassLoader
     * object. Namespace name is a key and path is a value.
     * Substring "%root%" will be substituted with path to
     * root directory of CFD installation and "%core%" substring
     * will be substituted with path to core files directory.
     * Core directory is assigned to \cfd\core namespace by default
     * so you don't have to list it in this array.
     */
    private static $sClassLoaderPaths = array(
        "cfd\\modules\\" => "%root%/modules/",
        "cfd\\modules\\" => "%root%/sites/all/modules/",
    );

    /**
     * @brief Static constructor.
     *
     * This function is called automatically. User doesn't
     * need to call it. This function sets main settings and
     * does some initial setup.
     */
    public static function __static() {
        self::$sRootDirectory = dirname(__FILE__);

        // setting of version and directory for core files and classes
        $ver = new \cfd\core\VersionChooser(self::$sRootDirectory . "/core");
        if( is_null(self::$sCoreVersion) ) {
            // we are suppose to pick up the newest version
            self::$sCoreVersion = $ver->getNewestVersion();
            self::$sCoreDirectory = $ver->getNewestVersionPath();
        }
        else {
            self::$sCoreDirectory = $ver->getSpecificVersionPath(self::$sCoreVersion);
        }
        if( !is_dir(self::$sCoreDirectory) ) {
            die("Low level fatal error: Directory with core files and classes does not exist: " . self::$sCoreDirectory);
        }

        // setting up ClassLoader global object
        require_once(self::getCoreDirectoryPath() . "/ClassLoader.php");
        foreach (self::$sClassLoaderPaths as $namespace => $path) {
            $path = str_replace("%root%", self::getRootDirectoryPath(), $path);
            $path = str_replace("%core%", self::getCoreDirectoryPath(), $path);
            \cfd\core\ClassLoader::getLoader()->addPath($namespace, $path);
        }

        // overriding PHP's function for autoloading
        function __autoload($className) {
            $retVal = \cfd\core\ClassLoader::$sClassAutoloaded->emit($className);
            if($retVal === false) {
                $classNameSize = strlen($className);
                $lastNsSeparatorI = strrpos($className, "\\");
                $namespaceName = substr($className, 0, $lastNsSeparatorI);
                $className = substr($className, $lastNsSeparatorI + 1, $classNameSize - $lastNsSeparatorI - 1);
                throw new \cfd\core\ClassNotFoundException(
                		"Desired class was not found and can not be loaded.",
                         $namespaceName, $className
                         );
            }
        }
    }

    /**
     * @return Version string for core files and classes.
     */
    public static function getCoreVersion() {
        return self::$sCoreVersion;
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
