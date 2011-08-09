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
 * @brief Class that defines db types.
 *
 * This class is used as abstract base for database system specific
 * classes (*DataType). These classes are suppose to compile CFD data type
 * represented by PHP variables to string that can be sent to database system
 * as data type declaration.
 */
abstract class DbDataType {
    /**
     * @brief Not defined type.
     *
     * This constant defines not defined type. If you
     * assign this to object you have to define it later
     * by type() function.
     */
    const UNDEFINED = 0;

    const INTEGER_8 = 1;
    const INTEGER_16 = 2;
    const INTEGER_24 = 3;
    const INTEGER_32 = 4;
    const INTEGER_64 = 5;

    const FLOAT_32 = 6;
    const FLOAT_64 = 7;

    const DECIMAL = 8;

    const TEXT_8 = 9;
    const TEXT_16 = 10;
    const TEXT_24 = 11;
    const TEXT_32 = 12;

    const BLOB_8 = 13;
    const BLOB_16 = 14;
    const BLOB_24 = 15;
    const BLOB_32 = 16;

    const CHAR = 17;
    const VARCHAR = 18;
    const ENUM = 19;
    const SET = 20;

    const DATE = 21;
    const TIME = 22;
    const DATETIME = 23;
    const TIMESTAMP = 24;

    private $mType = DbDataType::UNDEFINED;
    private $mDefaultVal = NULL;
    private $mIsUnsigned = false;
    private $mIsNullable = false;
    private $mIncrement = false;
    private $mSize = 0;
    private $mScale = 0;
    private $mSetArray = array();

    /**
     * @brief Creates new object.
     *
     * Creates new object. Created object can be adjusted by other class's
     * functions.
     *
     * @param integer $typeId Data type ID that will be used fot this object.
     */
    public function __construct($typeId = DbDataType::UNDEFINED) {
        $this->mType = $typeId;
    }

    /**
     * @brief Sets data type.
     *
     * Sets new data type for object. Compile function will return
     * string for this data type.
     *
     * @param integer $id Data type ID. See data type constants in this class.
     * @return Current object ($this).
     */
    public function type($id) {
        $this->mType = $id;
        return $this;
    }

    /**
     * @brief Gets object's type.
     *
     * @return @b Integer that is equal to one of the
     * constants in this class.
     */
    public function getType() {
        return $this->mType;
    }

    /**
     * @brief Sets default value.
     *
     * This function sets default value for this type.
     *
     * @param mixed $val Default value. If it is string put
     * it to single quotes inside string. If it is integer you
     * can use integer type here. Set to @b NULL to delete default
     * value.
     * @return Current object ($this).
     */
    public function defaultVal($val) {
        $this->mDefaultVal = $val;
        return $this;
    }

    /**
     * @brief Gets default value.
     *
     * @return Default value for type. This is @b NULL
     * if there is no default value.
     */
    public function getDefaulVal() {
        return $this->mDefaultVal;
    }

    /**
     * @brief Marks as unsigned type.
     *
     * This function makes this type unsigned type. If current
     * type has no unsigned equivalent this function has no efect.
     *
     * @param boolean $val If this is @b true unsigned mark is added.
     * If @b false type is marked as signed.
     * @return Current object ($this).
     */
    public function unsigned($val = true) {
        $this->mIsUnsigned = $val;
        return $this;
    }

    /**
     * @brief Returns unsigned mark.
     *
     * @return @b True if curretn object represent unsigned type,
     * @b false otherwise.
     */
    public function isUnsigned() {
        return $this->mIsUnsigned;
    }

    /**
     * @brief Marks as nullable type.
     *
     * Nullable type is type that can have a value @b NULL. If type
     * is nullable you do not have to list its value when inserting to
     * the table.
     *
     * @param boolean $val Set to @b true to turn nullable type on and to
     * @b false to turn it off (this is default from the begining of object).
     * @return Current object ($this).
     */
    public function nullable($val = true) {
        $this->mIsNullable = $val;
        return $this;
    }

    /**
     * @brief Finds out if type is nullable.
     *
     * @return @b True if type is nullable (set by previously call to nullable() function),
     * @b false if type can not take value of @b NULL.
     */
    public function isNullable() {
        return $this->mIsNullable;
    }

    /**
     * @brief Adjusts auto increment feature.
     *
     * This function turn auto incrementing of column on/off
     * and sets start value of auto incrementing.
     *
     * @param boolean $val @b True to turn on, @b false to turn off.
     * @param integer $from Start number from which incrementing starts.
     * @return Current object ($this).
     */
    public function increment($val = true, $from = 1) {
        $this->mIncrement = $val;
        $this->mIncrementFrom = $from;
        return $this;
    }

    /**
     * @brief Find out if auto incrementing state.
     *
     * @return @b True if its turn on (by increment() function), @b false otherwise.
     */
    public function isIncrement() {
        return $this->mIncrement;
    }

    /**
     * @brief Sets size for data type.
     *
     * This size is only assitant information for most types and can
     * be determined by extracting metadata from query's result set.
     * However this size is necessarry for folowing types:
     * @code
     *  \cfd\core\DbDataType::CHAR - sets maximum count of characters
     *  \cfd\core\DbDataType::VARCHAR - sets maximum count of characters
     *  \cfd\core\DbDataType::DECIMAL - sets count of all digits of real number that can be stored
     * @endcode
     *
     * @param integer $size Size that will be assigned to object's variable. Set to 0
     * to use database system's default size.
     * @return Current object ($this).
     * @see getSize()
     */
    public function size($size) {
        $this->mSize = $size;
        return $this;
    }

    /**
     * @brief Returns size parameter.
     *
     * @return @b Integer number that was previosly set by size() function.
     * When size() function wasn't used this returns 0.
     */
    public function getSize() {
        return $this->mSize;
    }

    /**
     * @brief Sets decimal precision.
     *
     * This function sets count of digit after decimal point. This
     * has only effect for fixed float type \\cfd\\core\\DataType::DECIMAL.
     *
     * @param integer $scale Count of digits after decimal point. Set to 0 if
     * you wish to use database system's default (this is default approach).
     * @return Current object ($this).
     */
    public function scale($scale) {
        $this->mScale = $scale;
        return $this;
    }

    /**
     * @brief Returns decimal precision.
     *
     * @return @b Integer number that corresponds to number that was previously
     * set by by scale() function. Returns 0 if scale() was never called.
     */
    public function getScale() {
        return $this->mScale;
    }

    /**
     * @brief Adds value for set/enum.
     *
     * This function has effect only for SET and ENUM type. It adds
     * value(s) that can be used for SET/ENUM type.
     *
     * @param mixed $val @b String which will be added as new value, or
     * @b array with strings whichs all values will be added as new values.
     * @return Current object ($this).
     */
    public function set($val) {
        if( is_array($val) ) {
            foreach($val as $toAdd) {
                $this->mSetArray[] = "'" . $toAdd . "'";
            }
        }
        else $this->mSetArray[] = "'" . $val . "'";
        return $this;
    }

    /**
     * @brief Returns array for SET/ENUM.
     *
     * @return @b Reference to internal array that contains all values
     * that can be add as SET/ENUM type's value.
     * @see set()
     */
    public function &getSetArray() {
        return $this->mSetArray;
    }

    /**
     * @brief Compiles type.
     *
     * Compiles type name and its info to form that is acceptable by
     * database system.
     *
     * @return @b String that have to be sent to database system. Actually
     * it is necessary to add it to query and send this full query. Note that
     * this output does not contains column name, replace @b !col with real name
     * before using the output.
     */
    public abstract function compile();

}
