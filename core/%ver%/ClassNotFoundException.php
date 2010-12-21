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

// autoloading doesn't work for this file because it's
// included in ClassLoader.php file
require_once("Exception.php");

/**
 * @brief Exception for class inclusion issue.
 *
 * This exception is thrown when ClassLoader fail to load
 * source file with desired class definition. Code of this
 * exception is @b 1 (returned by getCode() function)
 *
 * @see \cfd\core\ClassLoader
 */
class ClassNotFoundException extends \cfd\core\Exception {
    private $mClassName;

    /**
     * Constructs new object.
     *
     * @param string $msg Message that describes exception.
     * @param string $className Name of class that faild to load.
     * @param Exception $prev Previously thrown exception.
     */
    public function __construct($msg, $className, \Exception $prev = NULL) {
        parent::__construct($msg, 1, $prev);
        $this->mClassName = $className;
    }

    /**
     * @return Name of class (in string) that failed to load.
     */
    public function getClassName() {
        return $this->mClassName;
    }

}
