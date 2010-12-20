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

include_once("MainSettings.php");

echo "Core directory: " . MainSettings::$coreDirectory . "<br/>";
echo "Core version string: " . MainSettings::$coreVersion . "<br/>";
