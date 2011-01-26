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

use cfd\core\DbDriver;
require_once("MainSettings.php");

try {
    $obj = new cfd\core\NormalSignal();
    $obj->connect("cfd\core\I18n::tr");

    $val = 123321;
    echo "<pre>";
    var_dump( $obj->emit( 'This is string with variable with value @var!', array("@var" => $val) ) );
    echo "</pre>";

    $db = new DbDriver("mysql", "localhost", "cfd_test", "root", "root");
    $db->updateQuery("test_table_name", array("name" => "Richard", "address" => "Bratislava"), "name!='Richard'");
    $db->deleteQuery("test_table_name", "name='Richard'");
    $db->insertQuery("test_table_name", array("name" => "Risosssssss", "address" => "BA"));
    $res = $db->selectQuery("*", "test_table_name");
    while( ($row = $res->fetchRow()) !== false ) {
        echo $row["name"] . " lives in " . $row["address"] . "<br/>";
    }
}
catch(cfd\core\DbConnectionException $e) {
    $msg = cfd\core\I18n::tr("Exception was caught:\n");
    $msg .= cfd\core\I18n::tr("\tConnecting to database was not successful.\n");
    cfd\core\ExceptionHandling::handle($msg, $e);
}
catch(cfd\core\ClassNotFoundException $e) {
    $msg = cfd\core\I18n::tr("Exception was caught:\n");
    $msg .= cfd\core\I18n::tr("\tClass was not found in CFD's directories.\n");
    $className = $e->getNamespaceName() . "\\" . $e->getClassName();
    $msg .= cfd\core\I18n::tr( "\tFull class name is '!clsName'", array("!clsName" => $className) );
    cfd\core\ExceptionHandling::handle($msg, $e);
}
catch(Exception $e) {
    $msg = cfd\core\I18n::tr("Exception was caught by default catch block:\n");
    $msg .= "\t" . $e->getMessage();
    cfd\core\ExceptionHandling::handle($msg, $e);
}
