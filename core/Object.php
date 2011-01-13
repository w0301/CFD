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

require_once("BadTypeException.php");

/**
 * @brief Base class for almost all classes in CFD.
 *
 * This class provides necessary functions for object
 * communications and object's hierarchy (children and parents).
 */
class Object {
    private $mConnectedSignals = array();
    private $mChildren = array();
    private $mParent = NULL;

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
     * it disconnects it from all signals and do the same for
     * all children. It also said to parent that it should remove
     * this object from its children list. Simple said object stop
     * affecting CFD after this function is called on object.
     * Note that this function doesn't destroy the object in PHP.
     * To do so do this:
     * @code
     *  $obj->destroy();
     *  unset($obj); // this destroys $obj reference and calls
     *               // destructor if $obj was last reference
     * @endcode
     * Override this function if you need some extra work to be done
     * for your object class after object is deleted. If you do so don't
     * forget to call parent's destroy() function in your destroy() function.
     */
    public function destroy() {
        foreach($this->mConnectedSignals as $val) {
            $this->disconnectFromSignal($val);
            $val->disconnectAllFrom($this);
        }
        if( !is_null($this->mParent) ) {
            $this->mParent->removeChild($this);
        }
        foreach($this->mChildren as $child) {
            $child->destroy();
        }
    }

    /**
     * @brief Adds new child to object.
     *
     * This function adds given object to children list
     * and sets this object as parent of given object.
     *
     * @param object $child Object that will be added as child.
     */
    public function addChild(Object $child) {
        $this->mChildren[] = $child;
    }

    /**
     * @brief Removes child.
     *
     * This function removes given child from internal list
     * of children. Given child's parent will be set to NULL.
     *
     * @param object $child Child that will be removed.
     */
    public function removeChild(Object $child) {
        $key = false;
        if( ($key = array_search($child, $this->mChildren)) !== false ) {
            unset($this->mChildren[$key]);
            $child->setParent(NULL);
        }
    }

    /**
     * @brief Sets parent for object.
     *
     * This function sets parent for object. If there was
     * already any parent before calling this function it
     * first remove this object from its list.
     *
     * @param object $parent New parent for object must be
     * instance of \\cfd\\core\\Object or NULL (this is tested by type hinting).
     */
    public function setParent(Object $parent = NULL) {
        if( !is_null($this->mParent) ) {
            // we will prevent some pointless calls by this assigments
            $oldParent = $this->mParent;
            $this->mParent = NULL;
            $oldParent->removeChild($this);
        }
        $this->mParent = $parent;
        if( !is_null($this->mParent) ) {
            $this->mParent->addChild($this);
        }
    }

    /**
     * @return Parent of this object.
     */
    public function getParent() {
        return $this->mParent;
    }

    /**
     * @brief Constructs object.
     *
     * This constructor provides necessary initialization
     * of all objects. Please call it in your own constructors
     * in this way:
     * @code
     *  parent::__construct($parent);
     * @endcode
     *
     * @param object $parent Parent object that will add
     * newly created object to its children list. This must
     * be of type Object (see setParent()).
     *
     * @see setParent()
     */
    public function __construct(Object $parent = NULL) {
        $this->setParent($parent);
    }

    /**
     * @brief Destructs object.
     *
     * This do necessary clen up after all references
     * to object are destroyed. To delete object in CFD
     * mean call destroy() function. Allways call this
     * destructor if you declare your own destructor in child
     * classes:
     * @code
     *  parent::__destruct();
     * @endcode
     *
     * @see destroy()
     */
    public function __destruct() { }

}
