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
 * @brief Base class for almost all classes in CFD.
 *
 * This class provides necessary functions for object
 * communications and some other CFD mechanisms.
 */
class Object {
    private $mConnectedSignals = array();

    /**
     * @brief Connects object to signal.
     *
     * Creates a connection between signal and object.
     * This connection makes it possible to disconnect
     * object from signals when object is destroyed.
     *
     * @param object $sig Signal with which connection will be established.
     */
    protected function connectToSignal(Signal $sig) {
        if( ($key = array_search($sig, $this->mConnectedSignals)) === false ) {
            $this->mConnectedSignals[] = $sig;
        }
    }

    /**
     * @brief Disconnects object from signal.
     *
     * Remove previously created connection between object
     * and signal. This function calls signal's Signal::disconnectAllFrom()
     * function.
     *
     * @param object $sig Signal from which object will be disconnected.
     */
    protected function disconnectFromSignal(Signal $sig) {
        if( ($key = array_search($sig, $this->mConnectedSignals)) !== false ) {
            unset($this->mConnectedSignals[$key]);
        }
    }

    /**
     * @brief Destroys object.
     *
     * This function destroys object in CFD. This means that
     * it disconnects it from all signals. It doesn't destroy
     * the object in PHP to destroy object in PHP do this:
     * @code
     *  $obj->destroy();
     *  unset($obj); // this destroys $obj reference and calls
     *               // destructor if $obj was last reference
     * @endcode
     */
    public function destroy() {
        foreach($this->mConnectedSignals as $val) {
            $this->disconnectFromSignal($val);
            $val->disconnectAllFrom($this);
        }
    }

    /**
     * @brief Constructs object.
     *
     * This constructor provides necessary initialization
     * of all objects. Please call it in your own constructors
     * in this way:
     * @code
     *  parent::__construct();
     * @endcode
     */
    public function __construct() { }

    /**
     * @brief Destructs object.
     *
     * This destructor do necassary things that
     * have to be done after object destruction.
     * Note that destructor is called only if their
     * are not any reference to object. Remember that
     * reference to object is created each time object
     * is connected to signal. This means that only way
     * how to be sure that object's destructor is called
     * is to do this:
     * @code
     *  $obj->destroy();
     *  unset($obj); // this works only if $obj is last reference to object
     * @endcode
     *
     * @see destroy()
     */
    public function __destruct() { }

}
