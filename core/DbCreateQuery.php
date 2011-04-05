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
 * @brief Class for create query.
 *
 * This class provides abstract base for specific query classes.
 * Instance of this class is returned by \\cfd\\core\\DbDriver::create() function.
 */
abstract class DbCreateQuery extends DbQuery {
    /**
     * @brief Array with columns.
     *
     * This array contains info for all columns that should be
     * added to new table. Structure is:
     * @code
     *  $mColumns = array( "colName" => $db->dataType(...) );
     * @endcode
     */
    protected $mColumns = array();

    /**
     * @brief Name of primary key.
     *
     * This property contains name of column that should be
     * marked as primary key for table. If it is @b NULL there is
     * no such column.
     */
    protected $mPrimaryKeyColumn = NULL;

    /**
     * @brief List of foreign keys.
     *
     * This array contains all foreign keys for this table. Structure is:
     * @code
     *  $mForeignKeys = array(
     *   "%colName%" => array("table" => "%targetTableName%", "column" => "%targetColumnName%"[, "name" => "%nameOfKey%"])
     *  );
     * @endcode
     */
    protected $mForeignKeys = array();

    /**
     * @brief List of unique keys.
     *
     * This array contains all unique keys fot this table. Structure is:
     * @code
     *  $mUniqueKeys = array(
     *   "%colName%" [=> array("name" => "%nameOfKey%")]
     *  );
     */
    protected $mUniqueKeys = array();

    /**
     * @brief Create only if not exists.
     *
     * This property is @b true if db system should try to create
     * table only if it does not exist.
     */
    protected $mIfNotExists = false;

    /**
     * @brief Creates new object.
     *
     * @param string $tableName Name of table that will be created.
     * @param object $parent DbDriver object that created this object.
     */
    public function __construct($tableName, DbDriver $parent) {
        parent::__construct($tableName, NULL, $parent);
    }

    /**
     * @brief Sets if not exists property.
     *
     * This function turns creation only if not exists on/off.
     *
     * @param boolean $val @b True if you wish to turn it on, @b false otherwise.
     * @return Current object ($this).
     */
    public function ifNotExists($val = true) {
        $this->mIfNotExists = $val;
        $this->enforceCompilation();
        return $this;
    }

    /**
     * @brief Adds columns to be created.
     *
     * This function adds one or more columns to query. These columns will
     * be created as part of new table.
     *
     * @param array $cols This array contains columns' names and there
     * type. As key you have to use name in string and as value you have
     * to use object returned by \\cfd\\core\\DbDriver::dataType() function:
     * @code
     *  $arr = array( "id" => $db->dataType(cfd\core\DbDataType::INTEGER_32)->increment() );
     * @endcode
     * @return Current object ($this).
     */
    public function columns($cols) {
        foreach($cols as $key => $val) {
            $this->mColumns[$key] = $val;
        }
        $this->enforceCompilation();
        return $this;
    }


    /**
     * @brief Marks column as primary key.
     *
     * When this function is called column will be marked as
     * primary key. Note that it is possible to have just one primary
     * key per table. Calling this function twice cause overwriting primary key.
     *
     * @param string $colName Name of column that will be marked as primary key.
     * You have to add column with this name after or before calling this function,
     * otherwise database system error is possible.
     * @return Current object ($this).
     */
    public function primaryKey($colName) {
        $this->mPrimaryKeyColumn = $colName;
        $this->enforceCompilation();
        return $this;
    }

    /**
     * @brief Marks column(s) as foreign key.
     *
     * This function marks column(s) as reference to row in other table.
     * Table can have as many foreign keys as needed.
     *
     * @param array $colNames Keys in this array correspond to columns' names that
     * will be marked as foreign keys, values are info array. Full structure looks
     * like this:
     * @code
     *  $colNames = array(
     *   "%colName%" => array("table" => "%targetTableName%", "column" => "%targetColumnName%"[, "name" => "%nameOfKey%"])
     *  );
     * @endcode
     *
     * @return Current object ($this).
     */
    public function foreignKeys($colNames) {
        foreach($colNames as $key => &$val) {
            $this->mForeignKeys[$key] = $val;
        }
        $this->enforceCompilation();
        return $this;
    }

	/**
     * @brief Marks column(s) as unique.
     *
     * When column is marked as unique it's not possible to
     * insert more rows with same column value to the table.
     *
     * @param array $colNames Keys in this array are names of columns that
     * will be marked, values are optional - arrays with info. Structure is:
     * @code
     *  $colNames = array(
     *   "%colName%" [=> array("name" => "%nameOfKey%")]
     *  );
     * @endcode
     * @return Current object ($this).
     */
    public function uniqueKeys($colNames) {
        foreach($colNames as $key => &$val) {
            if( is_array($val) ) {
                // name of key is set by user
                $this->mUniqueKeys[$key] = $val;
            }
            else {
                // there is no key name + $val is our key now
                $this->mUniqueKeys[$val] = array();
            }
        }
        $this->enforceCompilation();
        return $this;
    }

}
