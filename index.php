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
    $obj = new cfd\core\NormalSignal();
    $obj->connect("cfd\core\I18n::tr");

    $val = 123321;
    echo "<pre>";
    var_dump( $obj->emit( 'This is string with variable with value @var!', array("@var" => $val) ) );
    echo "</pre>";

    $db = new cfd\core\DbDriver("mysql", "localhost", "cfd_test", "root", "root", "test_");

/*
    $res = $db->update("table4")->
            values( array("name" => "'Risko'", "address" => "'@val1'"), array("@val1" => "<city>London</city>") )->
            condition( $db->andCondition()->prop("id", array(70, 179), "BETWEEN") )->
            send();
    var_dump($res);
    echo "<br/><br/>";
*/
/*
    $res = $db->delete("table4")->
            condition( $db->andCondition()->prop("name", "'Rick'", "=") )->
            send();
    var_dump($res);
    echo "<br/><br/>";
*/
/*
    $res = $db->truncate("table4")->send();
    var_dump($res);
    echo "<br/><br/>";
*/
/*
    $res = $db->drop("table4", cfd\core\DbDropQuery::TABLE_DROP)->send();
    var_dump($res);
    echo "<br/><br/>";
*/
/*
    $res = $db->create("table4")->
            ifNotExists()->
            columns(
                array(
                    "id" => $db->dataType(cfd\core\DbDataType::INTEGER_32)->increment(),
                    "name" => $db->dataType(cfd\core\DbDataType::VARCHAR)->size(100),
                    "address" => $db->dataType(cfd\core\DbDataType::VARCHAR)->size(200)->nullable(),
                    "out_id" => $db->dataType(cfd\core\DbDataType::INTEGER_32)
                )
            )->
            primaryKey("id")->
            foreignKeys(
                array(
                    "out_id" => array("table" => "test_table1", "column" => "id", "name" => "forKey1")
                )
            )->
            uniqueKeys(
                array(
                    "name" => array("name" => "uniqueKey1"),
                    "address" => array("name" => "uniqueKey2")
                )
            )->
            send();
    var_dump($res);
    echo "<br/><br/>";
*/
/*
    $res = $db->insert("table4")->
            values( array("out_id" => 50, "name" => "'Richard Kakaš'", "address" => "'Bratislava'") )->
            send();
    var_dump($res);
    echo "<br/><br/>";
*/

    $res = $db->select("table4", "t1")->
            columns( array("id", "out_id", "name", "address") )->
            limit(0, 0)->
            send();
    while( ($row = $res->fetchRow(cfd\core\DbQueryResult::NAME_INDEXES)) !== false ) {
        print_r($row);
        echo "<br/>";
    }
    echo "<br/>";
}
catch(cfd\core\DbDriverException $e) {
    $msg = cfd\core\I18n::tr("Exception was caught:\n");
    if($e->getQuery() != "") {
        $msg .= cfd\core\I18n::tr("\tDatabase query was not successful.\n");
        $msg .= cfd\core\I18n::tr( "\tSQL query was: !s\n", array("!s" => $e->getQuery()) );
        $msg .= cfd\core\I18n::tr( "\tSQL error message: !s\n", array("!s" => $e->getMessage()) );
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
    $msg .= cfd\core\I18n::tr( "Message:\n\t!s", array("!s" => $e->getMessage()) );
    cfd\core\ExceptionHandling::handle($msg, $e);
}
