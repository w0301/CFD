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
 * @brief Class for update query.
 *
 * This class contains all function that can affect object
 * returned by \\cfd\\core\\DbDriver::update() function.
 *
 * @see \\cfd\\core\\DbDriver, \\cfd\\core\\DbQuery
 */
abstract class DbUpdateQuery extends DbQuery {
    /**
     * @brief Array with new values.
     *
     * This array contains new values for rows that are
     * affected by this query. Structure:
     * @code
     * 	$mNewValues = array(
     * 		array("column" => "columnName", "value" => "newValue")
     * 	);
     * @endcode
     */
    protected $mNewValues = array();

    /**
     * @brief Conditions for query.
     *
     * This object of type \\cfd\\core\\DbCondition is used to
     * determine which rows will be affected by this query.
     */
    protected $mCondition = NULL;

    /**
     * @brief Constructs new object.
     *
     * @param string $tableName Table name that will be affected by this query.
     * @param object $parent DbDriver that owns this query.
     */
    public function __construct($tableName, DbDriver $parent) {
        parent::__construct($tableName, NULL, $parent);
        $this->mCondition = new DbCondition("AND");
    }

    /**
     * @brief Sets new values.
     *
     * This function sets current values of columns in database to
     * passes values.
     *
     * @param array $vals Array with values. Key in array is string with
     * column name and key's value is new value for column.
     * @return Current object ($this).
     */
    public function values($vals) {
        foreach($vals as $key => $val) {
            $this->mNewValues[] = array("column" => $key, "value" => $val);
        }
        $this->enforceCompilation();
        return $this;
    }

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
    public function condition(DbCondition $cond) {
        $this->mCondition->condition($cond);
        return $this;
    }

}
