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

class I18n extends \cfd\core\I18n {
	// if you want you can change language settings by
	// overriding these functions
	public static function getLiteralsLocale() {
		return "sk";
	}
	public static function getPluralsExpression() {
		return "nplurals=3; plural=...;";	// replace '...' with right expression
	}

	public static function tr($strs, $n = 1) {
		return parent::translate("MyModule", $strs, $n);	// note the domain name
	}
}

function func($domainName, $strsLocale, $strs, $n, &$succed) {
    //$succed = false;
    return "$domainName -- $strsLocale -- $strs -- $n";
}

try {
    I18n::$sTranslateString->connect("func");
    echo I18n::tr( array($_SERVER['HTTP_ACCEPT_LANGUAGE'], "plural") ) . "<br/>";
    echo I18n::getLiteralsLocale() . "<br/>";
}
catch(\cfd\core\ClassNotFoundException $e) {
    echo "Class was not found in CFD directories (" . $e->getMessage() . "). <br/>";
    echo "Namespace of class: " . $e->getNamespaceName() . "<br/>";
    echo "Name of class: " . $e->getClassName() . "<br/>";
}
catch(\Exception $e) {
    echo "Exception occured: " . $e->getMessage() . "<br/>";
}
