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
 * @brief Truncate query class.
 *
 * Instance of this class is returned by \\cfd\\core\\DbDriver::truncate() function.
 */
abstract class DbTruncateQuery extends DbQuery {

    /**
     * @brief Creates new query object.
     *
     * @param string $tableName Name of table affected by query.
     * @param object $parent Object that created this query.
     */
    public function __construct($tableName, DbDriver $parent) {
        parent::__construct($tableName, NULL, $parent);
    }

}
