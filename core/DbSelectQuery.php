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
    private $mExpressionsCount = 0;

    /**
     * @brief Object of conditions.
     *
     * This is parent object of all conditions for this select
     * query. It's binary operator is set to "AND".
     *
     * @see \\cfd\\core\\DbCondition, condition()
     */
    protected $mCondition = NULL;

    /**
     * @brief Selects from limit.
     *
     * This number determine from which index rows should be selected.
     */
    protected $mLimitFrom = 0;

    /**
     * @brief Selects count limit.
     *
     * This number determine how many rows should be selected. Note that @b 0
     * means all rows.
     */
    protected $mLimitCount = 0;

    /**
     * @brief Array of columns.
     *
     * This array contains all columns for specific table that
     * are going to be selected.
     *
     * Structure of array:
     * @code
     *  $mColumns = array(
     *  	"tableName" => array("columns" => array("column1", "column2"), "all_columns" => false),
     *  	"tableName2" => array("columns" => array("this is ignored because 'all_columns' is true"), "all_columns" => true),
     *  );
     * @endcode
     *
     * @see columns()
     */
    protected $mColumns = array();

    /**
     * @biref Array of expressions.
     *
     * This array holds all expressions assigned to this query.
     * Each expression is specifed by expression string and alias
     * for expression return. Expressions are not portable across
     * different database systems. Example of expression:
     * @code
     *  COUNT(*) AS count_of_all
     * @endcode
     *
     * Structure of array:
     * @code
     *  $mExpressions = array(
     *  	"aliasName" => array("alias" => "aliasName", "expression" => "expressionString"),
     *  	...
     *  );
     * @endcode
     *
     * @see expression()
     */
    protected $mExpressions = array();

    public function __construct($tableName, DbDriver $parent) {
        parent::__construct($tableName, $parent);
        $this->mCondition = new DbCondition("AND");
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
        $this->enforceCompilation();
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
     * @brief Marks column(s) to be selected.
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
        if( !is_array($this->mColumns[$tableName]) ) {
            $this->mColumns[$tableName] = array("columns" => array(), "all_columns" => false);
        }
        if($columnNames == "*") {
            // indicates that all columns will be selected
            $this->mColumns[$tableName]["all_columns"] = true;
        }
        else if(!$this->mColumns[$tableName]["all_columns"]) {
            // add specified columns
            if( is_array($columnNames) ) {
                foreach($columnNames as &$val) {
                    $this->mColumns[$tableName]["columns"][] = $val;
                }
            }
            else {
                $this->mColumns[$tableName]["columns"][] = $columnNames;
            }
        }
        $this->enforceCompilation();
        return $this;
    }

    /**
     * @brief Adds expression to query.
     *
     * This function adds expression. Added expression is not changed to
     * be portable by CFD db system. Example of using this function:
     * @code
     *  $query->expression("COUNT(*)", "count_of_rows");
     * @endcode
     *
     * @param string $expStr String of expression.
     * @param string $alias Alias for expression call. You can use this as
     * column name when fetching values from db result. If this is @b NULL alias
     * will be autocreated in form - "expression_N", where N is count of autocreated aliases.
     * @param srray $args Variables that will be substituted from $expStr.
     * @return Current @b object ($this).
     */
    public function expression($expStr, $alias = NULL, $args = array()) {
        if( empty($alias) ) {
            // autogenerate alias
            $alias = "expression_" . $this->mExpressionsCount++;
            while( array_key_exists($alias, $this->mExpressions) ) {
                $alias = "expression_" . $this->mExpressionsCount++;
            }
            // alias is unique now we can continue
        }
        if( !empty($args) ) {
            DbDriver::filterVariables($args);
            $expStr = DbDriver::substituteVariables($expStr, $args);
        }
        $this->mExpressions[$alias] = array(
            "alias" => $alias,
            "expression" => $expStr
        );
        $this->enforceCompilation();
        return $this;
    }

    /**
     * @brief Adds new condition.
     *
     * Adds condition to where clause of select query. And returns
     * current object.
     *
     * @param object $cond Object that describes condition.
     * @return Current object ($this).
     * @see \\cfd\\core\\DbCondition
     */
    public function condition(DbCondition $cond) {
        $this->mCondition->condition($cond);
        return $this;
    }

    /**
     * @brief Sets new select limit.
     *
     * @param integer $from Index of first row to be selected.
     * @param integer $count Count of rows to select. Zero means all rows.
     * @return Current object ($this).
     */
    public function limit($from, $count = 0) {
        $this->mLimitFrom = $from;
        $this->mLimitCount = $count;
        return $this;
    }

}
