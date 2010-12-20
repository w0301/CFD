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

include_once("VersionChooser.php");

/**
 * @brief Holds main settings for CFD platform.
 *
 * This class is full of static properties. If you want
 * to adjust them you have to manually edit "MainSettings.php"
 * file by assigning default values to listed properties.
 */
class MainSettings {
	/**
	 * This variable specifies which version of core
	 * classes is being used. To use newest version leave
	 * without default value (this is default). If you want
	 * use specific version assign version string to this property.
	 */
    public static $coreVersion;

    /**
     * This variable specifies in which directory core
     * classes reside. This property is adjusted automatically
     * and can't be adjusted manually.
     */
    public static $coreDirectory;
}

// setting of version and directory for core files and classes
$ver = new \cfd\core\VersionChooser("./core/");
if( is_null(MainSettings::$coreVersion) ) {
    // we are suppose to pick up the newest version
    MainSettings::$coreVersion = $ver->getNewestVersion();
    MainSettings::$coreDirectory = $ver->getNewestVersionPath();
}
else {
    MainSettings::$coreDirectory = $ver->getSpecificVersionPath(MainSettings::$coreVersion);
}
if( !is_dir(MainSettings::$coreDirectory) ) {
    die("Low level fatal error: Directory with core files and classes does not exist: " . MainSettings::$coreDirectory);
}

