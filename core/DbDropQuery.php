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
    const TABLE_DROP = 1;

    /**
     * When used means that drop query will delete whole database.
     */
    const DATABASE_DROP = 2;

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
     *  \cfd\core\DbDropQuery::TABLE_DROP
     *  \cfd\core\DbDropQuery::DATABASE_DROP
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

    /**
     * @brief Gets type.
     *
     * @return Type of this drop query. One of these: (according to type)
     * @code
     *  \cfd\core\DbDropQuery::TABLE_DROP
     *  \cfd\core\DbDropQuery::DATABASE_DROP
     * @endcode
     */
    public function getType() {
        return $this->mType;
    }

    /**
     * @brief Gets name.
     *
     * @return Name of object (table or database) that will be
     * deleted by this query.
     */
    public function getName() {
        return $this->mName;
    }

}