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
 * @brief Constructs select query.
 *
 * This class can be used to construct select query for
 * database. It's abstract class that has to be extend by
 * driver specific query classes (which are private).
 * For this query class use \\cfd\\core\\DbDriver::select()
 * function to return instance of valid query class.
 *
 * @see \\cfd\\core\\DbDriver, \\cfd\\core\\DbQuery
 */
abstract class DbSelectQuery extends DbQuery {
    private $mOnlyDistinct = false;

    /**
     * @brief Array of columns.
     *
     * This array contains all columns for specific table that
     * are going to be selected. First index is table name and
     * second index is integer index of column name. Example:
     * @code
     *  // prints all columns that will be selected from "table1"
     *  foreach($this->mTablesColumns["table1"] as $val) {
     *  	echo $val . "\n";
     *  }
     *
     *  // if this is true we can't iterate over array because
     *  // there isn't any, string "all" indicates that all columns
     *  // should be selecter
     *  if($this->mTablesColumns["table1"] == "all") echo "all columns";
     * @endcode
     *
     */
    protected $mTablesColumns = array();

    public function __construct($tableName, DbDriver $parent) {
        parent::__construct($tableName, $parent);
    }

    /**
     * @brief Sets distinct selection.
     *
     * Use this function to on/off distinct selection.
     * If distinct selection is on, only columns that have
     * distinct value are selected.
     *
     * @param boolean $val Set to @b true if you want to turn selection
     * on or to @b false to turn it off.
     * @return Current @b object is returned (@b $this).
     */
    public function distinct($val = true) {
        $this->mOnlyDistinct = $val;
        return $this;
    }

    /**
     * @brief Returns only distinct.
     *
     * This function finds out if distinct selection is on or off.
     *
     * @return @b True if distinct selection is on, @b false otherwise.
     * @see distinct()
     */
    public function isOnlyDistinct() {
        return $this->mOnlyDistinct;
    }

    /**
     * @brief Mark column to be selected.
     *
     * This function marks column(s) to be selected by query.
     *
     * @param string $tableName Name of table which columns will be addded.
     * @param mixed $columnNames String with column name to be addded or array
     * with strings of columns names to be added. Set to "*" if you want to select
     * all columns (if you don't select any column this is done anyway).
     * @return Current @b object is returned (@b $this).
     */
    public function columns($tableName, $columnNames = "*") {
        if($columnNames == "*") $this->$mTablesColumns[$tableName] = "all";
        else {
            if( !is_array($this->$mTablesColumns[$tableName]) ) $this->$mTablesColumns[$tableName] = array();
            if( is_array($columnNames) ) {
                foreach($columnNames as &$val) {
                    $this->$mTablesColumns[$tableName][] = $val;
                }
            }
            else {
                $this->$mTablesColumns[$tableName][] = $columnNames;
            }
        }
        return $this;
    }

}
