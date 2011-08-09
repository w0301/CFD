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
 * @brief Class for drop query.
 *
 * Instance of this class is returned by \\cfd\\core\\DbDriver::drop() function.
 */
abstract class DbDropQuery extends DbQuery {
    /**
     * When used means that drop query will delete table.
     */
    const DROP_TABLE = 1;

    /**
     * When used means that drop query will delete whole database.
     */
    const DROP_DATABASE = 2;

    /**
     * @brief Type of drop query.
     *
     * Indicates what will should be deleted.
     */
    protected $mType;

    /**
     * @brief Name of object to delete.
     *
     * Object (table or database) with this name should be deleted.
     */
    protected $mName;

    /**
     * @brief Creates new query object.
     *
     * @param string $name Name of table or database that will be deleted.
     * @param integer $type Type of this query:
     * @code
     *  \cfd\core\DbDropQuery::DROP_TABLE
     *  \cfd\core\DbDropQuery::DROP_DATABASE
     * @endcode
     * @param object $parent Object that created this query.
     */
    public function __construct($name, $type, DbDriver $parent) {
        // we will use own vars for name and type storing in this class!
        parent::__construct(NULL, NULL, $parent);

        // adjusting class specific vars
        $this->mType = $type;
        $this->mName = $name;
    }

}
