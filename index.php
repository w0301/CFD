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


function foo(&$stopAfterThis, $i) {
    echo "foo() failed -> " . $i . "<br/>";
    $stopAfterThis = false;
}

function fo(&$stopAfterThis, $i) {
    echo "fo() failed -> " . $i . "<br/>";
    $stopAfterThis = false;
}


class FooClass extends \cfd\core\Object {
    public static function staticFunc(&$stopAfterThis, $i) {
        echo "FooClass::staticFunc() failed -> " . $i . "<br/>";
        $stopAfterThis = false;
    }
    public function normalFunc(&$stopAfterThis, $i) {
        echo "FooClassObj->normalFunc() failed -> " . $i . "<br/>";
        $stopAfterThis = false;
    }
    public function normalFunc2(&$stopAfterThis, $i) {
        echo "FooClassObj->normalFunc2() failed -> " . $i . "<br/>";
        $stopAfterThis = false;
    }
    public function normalFunc3(&$stopAfterThis, $i) {
        echo "FooClassObj->normalFunc3() failed -> " . $i . "<br/>";
        $stopAfterThis = false;
    }
}

try {
    $s = new cfd\core\ConditionalSignal();
    $s->connect("foo");
    $s->connect("fo");
    $s->connect("FooClass::staticFunc");

    $obj = new FooClass();
    $s->connect( array($obj, "normalFunc") );
    $s->connect( array($obj, "normalFunc2") );
    $s->connect( array($obj, "normalFunc3") );

    echo '<pre>';
    var_dump( $s->getConnectedFunctions() );
    echo '</pre>';

    //$obj->destroy();

    echo '<pre>';
    var_dump( $s->getConnectedFunctions() );
    echo '</pre>';

    var_dump( $s->emit(123321) );
}
catch(\cfd\core\ClassNotFoundException $e) {
    echo $e->getMessage() . " Class name: " . $e->getClassName() . "<br/>";
}
catch(\Exception $e) {
    echo $e->getMessage() . "<br/>";
}
