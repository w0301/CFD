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

require_once("Functionality.php");

/**
 * @brief Functionality for class loaders.
 *
 * This functionality is implemented by all class loaders.
 * Actually there is only one - \\cfd\\core\\DefaultClassLoader.
 * You will probably never need to implement this functionality in
 * your plugins but if you would like you can.
 *
 * Note that only \\cfd\\core\\Object subclasses can implement
 * this interface.
 *
 * @see \\cfd\\core\\DefaultClassLoader, \\cfd\\core\\ClassAutoloading
 */
interface ClassLoader extends Functionality {
    /**
     * @brief Function that loads classes.
     *
     * Function that implements this decalration has to load class
     * that is specified by given arguments.
     *
     * @param string $fullClassName Full qualified name of class (i.e. "\\namespaceName\\className").
     * @param boolean &$succeed Reference variable that has to be set to false if function
     * is not successful in class loading.
     */
    public function loadClass($fullClassName, &$succeed);

}
