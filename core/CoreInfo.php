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

namespace cfd\core;

/**
 * @brief Provides information about core files.
 *
 * This class provide some static functions that
 * returns useful information about core files and
 * classes such as core files directory and version.
 *
 * @see MainSettings
 */
class CoreInfo {
    /**
	 * This variable specifies version of core files.
	 * This is information that is used to determine
	 * modules compatibility => it's really important
	 * to update this variable whenever there might be
	 * any compatibility issues with core files.
	 */
    private static $sCoreVersion = "dev";

    /**
     * @return Version string of core files. Used mainly
     * for compatibility determination.
     */
    public static function getCoreVersion() {
        return self::$sCoreVersion;
    }

    /**
     * @return Path to directory where are core files located.
     */
    public static function getCoreDirectoryPath() {
        return \MainSettings::getCoreDirectoryPath();
    }

}
