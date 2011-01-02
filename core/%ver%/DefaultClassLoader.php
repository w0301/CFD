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

require_once("Object.php");
require_once("CoreInfo.php");
require_once("ClassLoader.php");

/**
 * @brief Used as default loader for classes.
 *
 * This class implements \\cfd\\core\\ClassLoader functionality.
 * It also provide default global loader for classes. This loader
 * can be returned by getLoader() function. If you want change class
 * loading process please try to change getLoader() object before you
 * create your own \\cfd\\core\\ClassLoader functionality implementation.
 *
 * @see \\cfd\\core\\ClassLoader
 */
class DefaultClassLoader extends Object implements ClassLoader {
    private static $sLoader;
    private $mPaths = array();

    private function includeClassFile($path, $className) {
        if($path == "") $path = "./";
        $fileName = $path;
        if($fileName[strlen($fileName) - 1] != "/") {
            $fileName .= "/";
        }
        $fileName .= $className . ".php";

        if( file_exists($fileName) ) {
            include_once($fileName);
            return true;
        }
        return false;
    }

    /**
     * Create new ClassLoader object.
     *
     * @param object $parent Parent of new object.
     */
    public function __construct(Object $parent = NULL) {
        parent::__construct($parent);
    }

    /**
     * Destructs ClassLoader object.
     */
    public function __destruct() {
        parent::__destruct();
    }

    /**
     * @brief Static constructor for class.
     *
     * This function is called right after class definition.
     * Its purpose is to initialize static variables (signals etc).
     * Never call this function by hand!
     */
    public static function __static() {
        if( is_object(self::$sLoader) ) return;

        // firstly initializing static variables
        self::$sLoader = new DefaultClassLoader();

        // initializing default global ClassLoader object
        self::$sLoader->addPath( "cfd\\core\\", CoreInfo::getCoreDirectoryPath() );
    }

    /**
     * Adds path for specific namespace.
     *
     * @param string $namespaceStr Namespace name for which path will be used.
     * @param string $pathStr Path that will be looked up for class files.
     */
    public function addPath($namespaceStr, $pathStr) {
        if( !array_key_exists($namespaceStr, $this->mPaths) ) {
            $this->mPaths[$namespaceStr] = array();
        }
        array_push($this->mPaths[$namespaceStr], $pathStr);
    }

    /**
     * Removes path for specific namespace.
     *
     * @param string $namespaceStr Namespace name for which path will be removed.
     * @param string $pathStr Path to remove.
     */
    public function removePath($namespaceStr, $pathStr) {
        if( array_key_exists($namespaceStr, $this->mPaths) ) {
            unset( $this->mPaths[$namespaceStr][array_search($pathStr, $this->mPaths[$namespaceStr])] );
            if(count($this->mPaths[$namespaceStr]) == 0) {
                unset( $this->mPaths[$namespaceStr] );
            }
        }
    }

    /**
     * Tries to load specific class using paths added by addPath() function.
     *
     * @param string $className Full qualified name of class (i.e. "\namespaceName\className").
     * @param boolean &$succeed Reference that is used to indicate signal if function
     * succeed in doing its job or not => if not signal will call early connected function.
     */
    public function loadClass($className, &$succeed) {
        if( ($pos = strrpos($className, "\\")) !== false ) {
            $namespaceName = substr($className, 0, $pos + 1);
            $className = substr($className, $pos + 1, strlen($className) - strlen($namespaceName));
            if( array_key_exists($namespaceName, $this->mPaths) ) {
                $included = false;
                foreach($this->mPaths[$namespaceName] as &$value) {
                    if( $this->includeClassFile($value, $className) ) {
                        $included = true;
                        break;
                    }
                }
                if($included == false) {
                    $succeed = false;
                }
            }
        }
    }

    /**
     * @brief Returns global class loader.
     *
     * @return Global loader for classes. If you want change global
     * loader behaviour always use this function to get loader object.
     */
    public static function getLoader() {
        return self::$sLoader;
    }

} DefaultClassLoader::__static();
