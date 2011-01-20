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

use cfd\core\BadTypeException;
require_once("MainSettings.php");

try {
    $obj = new cfd\core\NormalSignal();
    $obj->connect("cfd\core\I18n::tr");

    echo "<pre>";
    var_dump( $obj->emit( array($_SERVER['HTTP_ACCEPT_LANGUAGE'], "plural"), 1 ) );
    echo "</pre>";

    $db = new cfd\core\DbConnection("mysql:host=localhost;dbname=cfd_test", "root", "root");
    //var_dump( cfd\core\DbConnection::getPdoDrivers() );
}
catch(cfd\core\DbConnectionException $e) {
    $msg = cfd\core\I18n::tr("Exception was caught:\n");
    $msg .= cfd\core\I18n::tr("\tConnecting to database was not successful.\n");
    cfd\core\ExceptionHandling::handle($msg, $e);
}
catch(cfd\core\ClassNotFoundException $e) {
    $msg = cfd\core\I18n::tr("Exception was caught:\n");
    $msg .= cfd\core\I18n::tr("\tClass was not found in CFD's directories.\n");
    $msg .= "\tFull class name is '" . $e->getNamespaceName() . "\\" . $e->getClassName() . "'";
    cfd\core\ExceptionHandling::handle($msg, $e);
}
catch(Exception $e) {
    $msg = cfd\core\I18n::tr("Exception was caught by default catch block:\n");
    $msg .= "\t" . $e->getMessage();
    cfd\core\ExceptionHandling::handle($msg, $e);
}
