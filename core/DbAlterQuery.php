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

class DbAlterQuery extends DbQuery {
    const ADD_COLUMN = 1;
    const DROP_COLUMN = 2;
    const CHANGE_COLUMN = 3;

    protected $mType;

    /**
     * @brief Creates specific alter query.
     *
     * @param string $tableName Name of table that will be altered.
     * @param integer $alterType Type of query. Use any of class's constants.
     * @param object $parent Database driver that has created this query.
     */
    public function __construct($tableName, $alterType, DbDriver $parent) {
        parent::__construct($tableName, NULL, $parent);

        // setting internal type
        $this->mType = $alterType;
    }

}
