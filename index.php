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

use cfd\core\MySqlDataType;
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
    $res = $db->insert("table_name")->
            values( array("name" => "'Richard Kakaš'", "address" => "'Bratislava'") )->
            send();
    var_dump($res);
    echo "<br/>";
*/
/*
    $res = $db->update("table_name")->
            values( array("name" => "'Risko'", "address" => "'@val1'"), array("@val1" => "<city>London</city>") )->
            condition( $db->andCondition()->prop("id", array(70, 179), "BETWEEN") )->
            send();
    var_dump($res);
    echo "<br/>";
*/
/*
    $res = $db->delete("table_name")->
            condition( $db->andCondition()->prop("name", "'Rick'", "=") )->
            send();
    var_dump($res);
    echo "<br/>";
*/
/*
    $res = $db->truncate("table_name")->
            send();
    var_dump($res);
    echo "<br/>";
*/
/*
    $res = $db->drop("sss", cfd\core\DbDropQuery::DATABASE_DROP)->
            send();
    var_dump($res);
    echo "<br/>";
*/

    $res = $db->select("table_name", "t1")->
            columns( array("id", "name", "address") )->
            //expression( "COUNT(*)", "full_count" )->
            //distinct(true)->
            /*condition(
                $db->orCondition()->prop("name", "1", "=")->
                condition( $db->andCondition()->prop("name", "'Adam'", "=") )->
                condition( $db->andCondition()->prop("name", "'Ri%'", "LIKE") )
            )->*/
            //condition( $db->andCondition()->prop("t1.id", array(60, 61, 62), "NOT IN") )->
            //condition( $db->andCondition()->prop("t1.name", "'Rick'", "!=") )->
            limit(0, 0)->
            order("id", cfd\core\DbSelectQuery::DESC_ORDER)->
            order("name", cfd\core\DbSelectQuery::ASC_ORDER)->
            /*join(
                $db->select("table_name2", "t2")->columns( array("tel" => "phone") ),
                $db->andCondition()->prop("t1.id", "t2.t_id", "=")
            )->*/
            send();

    while( ($row = $res->fetchRow(cfd\core\DbQueryResult::NAME_INDEXES)) !== false ) {
        print_r($row);
        echo "<br/>";
    }
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
    $msg .= "\t" . $e->getMessage();
    cfd\core\ExceptionHandling::handle($msg, $e);
}
