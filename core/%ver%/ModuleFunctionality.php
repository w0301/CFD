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
 * This interface is base for all module functionality
 * interfaces. Such interfaces can be implemented by module's
 * main class to indicate that module is extending any functionality.
 * There should be one functionality interface for every feature that
 * needs some function calls after module is loaded (functions that has
 * to be called are registered by special function).
 *
 * This interface do not declare any functions. It is used only
 * for dynamic recognition of functionality interfaces of module's
 * main class.
 *
 * Some examples for functionalities are string translators.
 * These translators are implemented in modules and when these
 * modules are loaded their functionality function is connected
 * to right singnal.
 */
interface ModuleFunctionality {

}
