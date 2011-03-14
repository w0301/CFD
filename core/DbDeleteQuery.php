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
 * @brief Delete query class.
 *
 * Instance of this class is returned by DbDriver::delete() function.
 */
abstract class DbDeleteQuery extends DbQuery implements DbQueryWithCondition {

    /**
     * @brief Object for conditions.
     *
     * This object of type \\cfd\\core\\DbCondition holds
     * all condition assigned to this query.
     */
    protected $mCondition;

    public function condition(DbCondition $cond) {
        $this->mCondition->condition($cond);
        return $this;
    }

    /**
     * @brief Creates new query.
     *
     * @param string $tableName Name of table that will be affected by this query.
     * @param object $parent Object that created this query.
     */
    public function __construct($tableName, DbDriver $parent) {
        parent::__construct($tableName, NULL, $parent);

        // creating objects
        $this->mCondition = DbCondition::andCondition();
    }

}
