<?php
/*
 * Copyright (C) 2011 Richard Kakaš.
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
 * @brief Base interface for all functionalities.
 *
 * This interface is base for all functionality
 * interfaces. Such interfaces can be implemented by any
 * class to indicate that clas is extending any functionality.
 * There should be one functionality interface for every feature that
 * can be extended by this way. Functions that connects
 * functionality's implementers to feature are always static functions and they
 * are declared in functionality owner class (for example \\cfd\\core\\I18n or
 * \\cfd\\core\\ClassAutoloading).
 *
 * This interface do not declare any functions. It is used only
 * for dynamic recognition of functionality interfaces of module's
 * main class.
 *
 * Some examples for functionalities are string translators.
 * These translators are implemented in module's plugins and when these
 * plugins are loaded their functionality function is connected
 * to right signal.
 */
interface Functionality {

}
