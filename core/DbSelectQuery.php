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
 * @brief Class for select query.
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
    /**
     * Indicates ascending ordering.
     */
    const ASC_ORDER = 1;

    /**
     * Indicates descending ordering.
     */
    const DESC_ORDER = 2;

    /**
     * Indicates inner join in join() function.
     */
    const INNER_JOIN = 1;

    /**
     * Indicates left join in join() function.
     */
    const LEFT_JOIN = 2;

    /**
     * Indicates right join in join() function.
     */
    const RIGHT_JOIN = 3;

    /**
     * Indicates full join in join() function.
     */
    const FULL_JOIN = 4;

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
     *  	"all_columns" => false,
     *  	array("name" => "col1", "alias" => "c1"),
     *  	array("name" => "col2", "alias" => NULL)
     *  );
     * @endcode
     *
     * @see columns()
     */
    protected $mColumns = array();

    /**
     * @brief Array of expressions.
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

    /**
     * @brief Array of order informations.
     *
     * This array contains column names according
     * to which ordering should be done. It also contains
     * ordering type for each column:
     * @code
     * 	$mOrdering = array(
     * 		array("column" => "id", "type" => DbSelectQuery::ASC_ORDER),
     * 		array("column" => "name", "type" => DbSelectQuery::DESC_ORDER)
     * 	);
     * @endcode
     */
    protected $mOrdering = array();

    /**
     * @brief Array of joined queries.
     *
     * This array contains information about all select queries that
     * should be joined with this query:
     * @code
     * 	$mJoins = array(
     * 		array("query" => new DbSelectQuery(), "type" => DbSelectQuery::INNER_JOIN, "on" => new DbCondition())
     * 	);
     * @endcode
     */
    protected $mJoins = array();

    /**
     * @brief Returns array with all columns.
     *
     * This function join column arrays of all joined tables
     * with column array of current query. Array structure:
     * @code
     * 	$arr = array(
     * 		array("name" => "columnName", "alias" => "alias"),
     * 		array("name" => "columnName2", "alias" => NULL)
     * 	);
     * @endcode
     *
     * @return Array that contains all columns that should be selected.
     */
    protected function getAllColumns() {
        $resArr = array();
        if($this->mColumns["all_columns"]) {
            // we just add "table.*" column, with out alias!
            $add = "";
            if( is_null( $this->getTableNameAlias() ) ) $add .= $this->getTableName();
            else $add .= $this->getTableNameAlias();
            $add .= ".*";
            $resArr[] = array("name" => $add, "alias" => NULL);
        }
        else {
            // we have to add all columns with apropirate aliases
            foreach($this->mColumns as $key => $val) {
                if( is_array($val) ) {
                    $add = "";
                    if( is_null( $this->getTableNameAlias() ) ) $add .= $this->getTableName();
                    else $add .= $this->getTableNameAlias();
                    $add .= "." . $val["name"];
                    $resArr[] = array("name" => $add, "alias" => $val["alias"]);
                }
            }
        }

        // now adding columns from joind queries
        foreach($this->mJoins as &$val) {
            $toAddArr = $val["query"]->getAllColumns();
            foreach($toAddArr as $col) {
                $resArr[] = $col;
            }
        }

        return $resArr;
    }

    /**
     * @brief Returns all conditions.
     *
     * This function adds all conditions to one and return
     * coressponding condition object.
     *
     * @return @b Object of \\cfd\\core\\DbCondition type.
     */
    protected function getAllConditions() {
        // firsly clone current condition
        $resCond = clone $this->mCondition;

        // after that adds condition of joined queries
        foreach($this->mJoins as &$val) {
            $cond = $val["query"]->getAllConditions();
            if( !$cond->isEmpty() ) $resCond->condition($cond);
        }

        return $resCond;
    }

    /**
     * @brief Returns all conditions.
     *
     * Merge all expression arrays of all joined queries with
     * current expression array. Returned array structure:
     * @code
     * 	$arr = array(
     * 		array("expression" => "expStr", "alias" => "aliasStr")
     * 	);
     * @endcode
     *
     * @return @b Array with all expressions.
     */
    protected function getAllExpressions() {
        $resArr = array();

        // firstly adding current expressions
        foreach($this->mExpressions as $exp) {
            $resArr[] = $exp;
        }

        // and now adding expressions of joined queries
        foreach($this->mJoins as &$val) {
            $toAddArr = $val["query"]->getAllExpressions();
            foreach($toAddArr as $exp) {
                $resArr[] = $exp;
            }
        }

        return $resArr;
    }

    /**
     * @brief Returns all joins.
     *
     * Merge all joins arrays recursively.
     *
     * @return @b Array with all joins.
     */
    protected function getAllJoins() {
        $resArr = array();

        // firstly adding current expressions
        foreach($this->mJoins as &$join) {
            $resArr[] = $join;
        }

        // and now adding expressions of joined queries
        foreach($this->mJoins as &$val) {
            $toAddArr = $val["query"]->getAllJoins();
            foreach($toAddArr as &$join) {
                $resArr[] = $join;
            }
        }

        return $resArr;
    }

    /**
     * @brief Constructs new query.
     *
     * Creates new select query.
     *
     * @param string $tableName Name of table that this query selects from.
     * @param string $tableAlias Alias for table name, or @b NULL if alias is not needed.
     * @param object $parent \\cfd\\core\\DbDriver object that sends this query.
     */
    public function __construct($tableName, $tableAlias, DbDriver $parent) {
        parent::__construct($tableName, $tableAlias, $parent);

        // condition for query
        $this->mCondition = new DbCondition("AND");

        // columns names
        $this->mColumns["all_columns"] = false;
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
     * @brief Adds ordering parameter.
     *
     * This function adds column with specified ordering type to
     * internal array which will be processed by compile() function.
     *
     * @param string $column Name of column according to which ordering will occur.
     * @param integer $type Type of ordering. Can be \\cfd\\core\\DbSelectQuery::ASC_ORDER
     * or \\cfd\\core\\DbSelectQuery::DESC_ORDER.
     * @return Current query object ($this).
     */
    public function order($column, $type = DbSelectQuery::ASC_ORDER) {
        $this->mOrdering[] = array("column" => $column, "type" => $type);
        return $this;
    }

    /**
     * @brief Adds query to be joined.
     *
     * Adds select query object to internal array for joins. All queris
     * will be joined to query during compile() function.
     *
     * @param object $query Select query object that will be joined. All where conditions
     * of joined query will be added to host query (only during compilation). Also all columns
     * from joined query will be selected by host query.
     * @param object $on Condition that handle join. (after "ON" keyword in SQL)
     * @param integer $type Type of join. Can be one of following:
     * @code
     *  \cfd\core\DbSelectQuery::INNER_JOIN
     *  \cfd\core\DbSelectQuery::LEFT_JOIN
     *  \cfd\core\DbSelectQuery::RIGHT_JOIN
     *  \cfd\core\DbSelectQuery::FULL_JOIN
     * @endcode
     * @return Current object ($this).
     */
    public function join(DbSelectQuery $query, DbCondition $on, $type = DbSelectQuery::INNER_JOIN) {
        $this->mJoins[] = array(
            "query" => $query,
            "on" => $on,
            "type" => $type
        );
        return $this;
    }

    /**
     * @brief Marks column(s) to be selected.
     *
     * This function marks column(s) to be selected by query.
     * Note that if you define alias name for column you have to
     * index column's value by alias and not by column's name!
     *
     * @param mixed $columnNames String with column name to be addded or array
     * with strings of columns names to be added. If you use array you can use
     * array with just values (each value is column name) or you can specify key
     * as string (key is used as column name and value as alias name).
     * Set to "*" if you want to select all columns (if you don't select any column this is done anyway).
     * @return Current @b object is returned (@b $this).
     */
    public function columns($columnNames = "*") {
        if( is_string($columnNames) ) {
            if($columnNames == "*") $this->mColumns["all_columns"] = true;
            else $this->mColumns[] = array("name" => $columnNames, "alias" => NULL);
        }
        else if( is_array($columnNames) && !empty($columnNames) ) {
            foreach($columnNames as $key => $val) {
                // key is col name and val is alias
                if( is_string($key) && is_string($val) ) {
                    $this->mColumns[] = array("name" => $key, "alias" => $val);
                }
                else if( is_string($val) ) {
                    $this->mColumns[] = array("name" => $val, "alias" => NULL);
                }
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
