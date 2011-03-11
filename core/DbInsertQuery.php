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
 * @brief Class for insert query.
 *
 * This class is used to create object in \\cfd\\core\\DbDriver::insert()
 * function. Use class's fnuction to adjust properties of object returned
 * by that function.
 *
 * @see \\cfd\\core\\DbDriver::insert(), \\cfd\\core\\DbQuery
 */
abstract class DbInsertQuery extends DbQuery {
    /**
     * @brief Array with values.
     *
     * This array contains column names and values for columns
     * that will be inserted to table by this query.
     *
     * Structure of array:
     * @code
     * 	$mValues = array(
     * 		array("column" => "columnName", "value" => "valueForColumn")
     * 	);
     * @endcode
     */
    protected $mValues = array();

    /**
     * @brief Creates new query.
     *
     * @param string $tableName Name of table.
     * @param object $parent Database driver that created this query.
     */
    public function __construct($tableName, DbDriver $parent) {
        parent::__construct($tableName, NULL, $parent);
    }

    /**
     * @brief Sets values.
     *
     * This function sets passed values to be written to database.
     *
     * @param array $vals Array with values. Key in array is string with
     * column name and key's value is value that will be inserted.
     * @return Current object ($this).
     */
    public function values($vals) {
        foreach($vals as $key => $val) {
            $this->mValues[] = array("column" => $key, "value" => $val);
        }
        $this->enforceCompilation();
        return $this;
    }

}
