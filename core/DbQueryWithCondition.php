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
 * @brief Interface for query classes.
 *
 * This is common interface for classes that are suppose to
 * do job according to conditions that have to met by row in
 * database. For this job there is condition() function.
 *
 * @see DbSelectQuery, DbUpdateQuery, DbDeleteQuery
 */
interface DbQueryWithCondition {

    /**
     * @brief Adds new condition.
     *
     * Condition is added to internal object. Use functions
     * \\cfd\\core\\DbCondition::andCondition() and/or \\cfd\\core\\DbCondition::orCondition()
     * to create object that will be passed to this function.
     *
     * @param object $cond Condition object that will be added
     * as condition to query.
     * @return Curretn query object ($this).
     */
    public function condition(DbCondition $cond);

}
