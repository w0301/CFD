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
 * @brief Works with database system return.
 *
 * This class is used to iterate over all rows returned by
 * database system after any query. Every specific database system
 * driver has to have an implementation of this class and has to use
 * it for returning from own query() function.
 *
 * @see \\cfd\\core\\DbDriver
 */
interface DbQueryResult {
    /**
     * Indicates using of number indexes for fetchRow() function.
     */
    const NUM_INDEXES = 1;

    /**
     * Indicates using of name indexes for fetchRow() function.
     */
    const NAME_INDEXES = 2;

    /**
     * Indicates using of number and name indexes for fetchRow() function.
     */
    const BOTH_INDEXES = 3;

    /**
     * @brief Returns values of current row.
     *
     * This function returns data in current row in PHP array
     * and moves index of current row forward. Indexes for specific
     * columns are declared according to given argument.
     *
     * Example of using function:
     * @code
     *  while( ($arr = $res->fetchRow()) != false ) {
     *      echo $arr["columnName"] . "\n";
     *  }
     * @endcode
     *
     * @param integer $type Type of indexes that can be used to return
     * array's data:
     * @code
     *  \cfd\core\DbQueryResult::NUM_INDEXES - numbers will be used as indexes, starting at 0
     *  \cfd\core\DbQueryResult::NAME_INDEXES - case-sensitive name of columns will be used as index
     *  \cfd\core\DbQueryResult::BOTH_INDEXES - both, numbers and names, will be used as indexes
     * @endcode
     * @return @b Array that can be used to get columns values, or @b false if there is no
     * row anymore.
     */
    public function fetchRow($type = self::NAME_INDEXES);

    /**
     * @brief Returns number of rows.
     *
     * This function returns number of rows that were returned by query.
     *
     * @return @b Integer that corresponds to rows count.
     */
    public function getRowsCount();

    /**
     * @brief Returns number of columns.
     *
     * This function returns number of columns that were returned by query.
     *
     * @return @b Integer that corresponds to columns count.
     */
    public function getColumnsCount();

}
