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
    echo "Root directory: " . MainSettings::getRootDirectoryPath() . "<br/>";
    echo "Core directory: " . \cfd\core\CoreInfo::getCoreDirectoryPath() . "<br/>";
    echo "Core version string: " . \cfd\core\CoreInfo::getCoreVersion() . "<br/>";
}
catch(\cfd\core\ClassNotFoundException $e) {
    echo $e->getMessage() . " Class name: " . $e->getClassName() . "<br/>";
}
catch(\Exception $e) {
    echo $e->getMessage() . "<br/>";
}
