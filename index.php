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
    //$db->updateQuery("test_table_name", array("name" => "Richard", "address" => "Bratislava"), "name!='Richard'");
    //$db->deleteQuery("test_table_name", "name='Richard'");
    //$db->insertQuery("test_table_name", array("name" => "Adam", "address" => "Ahem"));
    //$res = $db->selectQuery("*", "test_table_name");
    //$res = $db->query("SELECT test_table_name.name, new_table.text FROM test_table_name, new_table");

    $res = $db->select("test_table_name", "t")->
            columns( array("id", "name", "address") )->
            //expression( "COUNT(*)", "full_count" )->
            //distinct(true)->
            condition(
                cfd\core\DbCondition::orCondition()->prop("name", "1", "=")->
                condition( cfd\core\DbCondition::andCondition()->prop("name", "'Adam'", "=") )->
                condition( cfd\core\DbCondition::andCondition()->prop("name", "'Ri%'", "LIKE") )
            )->
            condition( cfd\core\DbCondition::andCondition()->prop("t.id", array(60, 65), "BETWEEN") )->
            limit(0, 0)->
            order("id", cfd\core\DbSelectQuery::ASC_ORDER)->
            order("name", cfd\core\DbSelectQuery::DESC_ORDER)->
            send();

    while( ($row = $res->fetchRow(cfd\core\DbQueryResult::NAME_INDEXES)) !== false ) {
        print_r($row);
        echo "name: " . $row["name"] . "<br/>";
    }
}
catch(cfd\core\DbDriverException $e) {
    $msg = cfd\core\I18n::tr("Exception was caught:\n");
    if($e->getQuery() != "") {
        $msg .= cfd\core\I18n::tr("\tDatabase query was not successful.\n");
        $msg .= cfd\core\I18n::tr( "\tSQL query was: !s\n", array("!s" => $e->getQuery()) );
    }
    else {
        $msg .= cfd\core\I18n::tr("\tDatabase connection can't be established.\n");
    }
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
