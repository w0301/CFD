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
 * @brief Condition for db queries.
 *
 * This class is used for building conditions for Db*Query classes.
 *
 * @see \\cfd\\core\\DbQuery, \\cfd\\core\\DbDriver
 */
class DbCondition extends Object {
    private $mNeedCompilation = true;
    private $mLastCompileOutput = NULL;
    private $mBinOperator = NULL;
    private $mVariable = NULL;
    private $mOperator = NULL;
    private $mValue = NULL;

    private function getStringForValue() {
        // returns string that has to be used as right side of operator statement
        $res = "";
        if( is_array($this->mValue) && ($this->mOperator == "IN" || $this->mOperator == "NOT IN") ) {
            $res .= "(";
            $done = 0;
            $size = count($this->mValue);
            foreach($this->mValue as $val) {
                if( is_integer($val) || is_float($val) ) $res .= $val;
                else $res .= "'" . $val . "'";
                if(++$done != $size) $res .= ", ";
            }
            $res .= ")";
        }
        else if( is_array($this->mValue) ) {
            $done = 0;
            $size = count($this->mValue);
            foreach($this->mValue as $val) {
                if( is_integer($val) || is_float($val) ) $res .= $val;
                else $res .= "'" . $val . "'";
                if(++$done != $size) $res .= " AND ";
                if($done == 2) break;
            }
        }
        else $res = $this->mValue;

        return $res;
    }

    /**
     * @brief Creates new condition object.
     *
     * Creates new condition with given binary operator that will
     * be used as subconditions delimiter.
     *
     * @param string $binOperator Operator that will be between all
     * sub condition of this condition. Can be "AND" or "OR".
     * @param object $parent Parent of this condition.
     */
    public function __construct($binOperator = "AND", Object $parent = NULL) {
        parent::__construct($parent);
        $this->mBinOperator = $binOperator;
    }

    /**
     * @brief Is condition empty?
     *
     * @return @b True if condition is empty, @b false otherwise.
     */
    public function isEmpty() {
        return count( $this->getChildren() ) == 0 &&
            ( empty($this->mVariable) || empty($this->mOperator) || empty($this->mValue) );
    }

    /**
     * @brief Sets properties.
     *
     * This function sets default properties for this object. Note
     * that properties of subconditions are appended to current object's
     * properties during compilation of condition. Note that if you want to
     * pass string $variable or $value value you have to use single quotes
     * around the string.
     *
     * @param string $variable Name of variable, or any other left operand of condition.
     * @param string $value Value of variable to be test, or any other right operand. For
     * 'BETWEEN' and 'IN' operators this has to be array.
     * @param string $operator Operator to be used between $variable and $value.
     * Folowing operators are supported:
     * @code
     *	'='
     *	'<> or '!='
     *	'>'
     *	'<'
     *	'>='
     *	'<='
     *	'BETWEEN' and 'BETWEEN'
     *	'LIKE' and 'NOT LIKE'
     *	'IN' and 'NOT IN'
     * @endcode
     * @return Current object ($this).
     */
    public function prop($variable, $value, $operator = "=") {
        $this->mVariable = $variable;
        $this->mValue = $value;
        $this->mOperator = $operator;
        $this->mNeedCompilation = true;
        return $this;
    }

    /**
     * @brief Adds subcondition.
     *
     * Almost alias for addChild() function.
     *
     * @param object $cond Condition to be added.
     * @return Current object ($this).
     */
    public function condition(DbCondition $cond) {
        $this->addChild($cond);
        return $this;
    }

    /**
     * @brief Compiles condition.
     *
     * Compiles condition to format accepted by most database system.
     * Compilation is performed only if it is needed.
     *
     * @return @b String that contains compilation output.
     */
    public function compile() {
        if($this->mNeedCompilation) {
            $this->mLastCompileOutput = "";

            // count of children
            $children =& $this->getChildren();
            $size = count($children);

            // empty operator means '='
            if( empty($this->mOperator) ) $this->mOperator = "=";

            // adding current props
            $isAllSet = !empty($this->mVariable) && !empty($this->mValue);
            if($isAllSet) {
                if($size > 0) $this->mLastCompileOutput .= "(";
                $this->mLastCompileOutput .= $this->mVariable;
                $this->mLastCompileOutput .= " " . $this->mOperator . " ";
                $this->mLastCompileOutput .= $this->getStringForValue();
                if($size > 0) {
                    $this->mLastCompileOutput .= ")";
                    $this->mLastCompileOutput .= " " . $this->mBinOperator . " ";
                }
            }

            // adding props of children
            $done = 0;
            foreach($children as $child) {
                $this->mLastCompileOutput .= "(" . $child->compile() . ")";
                if(++$done != $size)  $this->mLastCompileOutput .= " " . $this->mBinOperator . " ";
            }
        }
        return $this->mLastCompileOutput;
    }

    /**
     * @brief Creates new condition object.
     *
     * Creates condition object with "AND" binary operator.
     *
     * @return New condition object.
     */
    public static function andCondition() {
        return new DbCondition("AND");
    }

    /**
     * @brief Creates new condition object.
     *
     * Creates condition object with "OR" binary operator.
     *
     * @return New condition object.
     */
    public static function orCondition() {
        return new DbCondition("OR");
    }
}
