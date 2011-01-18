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

use cfd\core\DbConnection;
require_once("MainSettings.php");

try {
    $obj = new cfd\core\NormalSignal();
    $obj->connect("cfd\core\I18n::tr");

    echo "<pre>";
    var_dump( $obj->emit( array($_SERVER['HTTP_ACCEPT_LANGUAGE'], "plural"), 1 ) );
    echo "</pre>";

    $db = new cfd\core\DbConnection("mysql:host=localhost;dbname=c", "root", "root");
}
catch(cfd\core\DbConnectionException $e) {
    echo "Connection to database failed (" . $e->getMessage() . "). <br/>";
}
catch(cfd\core\ClassNotFoundException $e) {
    echo "Class was not found in CFD directories (" . $e->getMessage() . "). <br/>";
    echo "Namespace of class: " . $e->getNamespaceName() . "<br/>";
    echo "Name of class: " . $e->getClassName() . "<br/>";
}
catch(Exception $e) {
    echo "Exception occured: " . $e->getMessage() . "<br/>";
}
