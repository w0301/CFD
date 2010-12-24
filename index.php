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

function fo($i) {
    echo "fo() -> " . $i . "<br/>";
    return true;
}

function foo($i) {
    echo "foo() -> " . $i . "<br/>";
    return true;
}

class FooClass {
    public static function staticFunc($i) {
        echo "FooClass::staticFunc() -> " . $i . "<br/>";
        return true;
    }
    public function normalFunc($i) {
        echo "FooClassObj->normalFunc() -> " . $i . "<br/>";
        return true;
    }
    public function normalFunc2($i) {
        echo "FooClassObj->normalFunc2() -> " . $i . "<br/>";
        return true;
    }
    public function normalFunc3($i) {
        echo "FooClassObj->normalFunc3() -> " . $i . "<br/>";
        return true;
    }
}

try {
    echo "Root directory: " . MainSettings::getRootDirectoryPath() . "<br/>";
    echo "Core directory: " . \cfd\core\CoreInfo::getCoreDirectoryPath() . "<br/>";
    echo "Core version string: " . \cfd\core\CoreInfo::getCoreVersion() . "<br/>";

    $s = new cfd\core\NormalSignal();
    $s->connect("foo");
    $s->connect("fo");
    $s->connect("FooClass::staticFunc");

    $obj = new FooClass();
    $s->connect( array($obj, "normalFunc") );
    $s->connect( array($obj, "normalFunc2") );
    $s->connect( array($obj, "normalFunc3") );

    echo "<br/><br/>";
    var_dump( $s->getConnectedFunctions() );

    //$s->disconnectAllFrom($obj);

    echo "<br/><br/>";
    var_dump( $s->getConnectedFunctions() );

    echo "<br/><br/>";
    var_dump( $s->emit(123456) );
}
catch(\cfd\core\ClassNotFoundException $e) {
    echo $e->getMessage() . " Class name: " . $e->getClassName() . "<br/>";
}
catch(\Exception $e) {
    echo $e->getMessage() . "<br/>";
}
