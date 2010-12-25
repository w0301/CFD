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

require_once("MainSettings.php");

try {

}
catch(\cfd\core\ClassNotFoundException $e) {
    echo "Class was not found in CFD directories (" . $e->getMessage() . "). <br/>";
    echo "Namespace of class: " . $e->getNamespaceName() . "<br/>";
    echo "Name of class: " . $e->getClassName() . "<br/>";
}
catch(\Exception $e) {
    echo "Exception occured: " . $e->getMessage() . "<br/>";
}
