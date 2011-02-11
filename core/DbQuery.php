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
 * @brief Base class for db queries.
 *
 * This is base class for all database query classes. These specific
 * query classes are query depended and they have to be extened for every
 * single database system. Specific query classes are called *DbQuery where
 * '*' stands for query name.
 *
 * This class represents only query interface plus few common functions.
 *
 * @see \\cfd\\core\\DbDriver
 */
abstract class DbQuery extends Object {
    const SELECT_QUERY = 1;
    const INSERT_QUERY = 2;
    const UPDATE_QUERY = 3;
    const DELETE_QUERY = 4;
    const CREATE_QUERY = 5;
    const TRUNCATE_QUERY = 6;
    const ALTER_QUERY = 7;
    const DROP_QUERY = 8;

    private $mTableNames = array();
    private $mLastCompileOutput = NULL;
    private $mWasChanged = false;

    /**
     * @brief Constructs new query object.
     *
     * This constructor constructs new object. Note that
     * this class extends \\cfd\\core\\Object class so we
     * can use object hierarchy for specifing which driver
     * object owns this query and which driver should be asked
     * to send it to database system.
     *
     * @param string $tableName String with name of table that will be affected
     * by this query.
     * @param object $parent DbDriver object that owns this
     * query (DbDriver object that sent it).
     * @see getDbDriver()
     */
    public function __construct($tableName, DbQuery $parent) {
        parent::__construct($parent);
        $this->mTableNames[] = $tableName;
    }

    /**
     * @brief Returns owner.
     *
     * This is just synonym for getParent() function.
     *
     * @return @b Object of type \\cfd\\core\\DbDriver that owns
     * this query object.
     */
    public function getDbDriver() {
        return getParent();
    }

    /**
     * @brief Returns primary table name.
     *
     * This function returns primary table name that is going to be
     * affected by this query.
     *
     * @return @b String with table name that was passed to object's constructor.
     */
    public function getPrimaryTableName() {
        return $this->mTableNames[0];
    }

    /**
     * @brief Returns all table names.
     *
     * This function can be used to return all table names that are
     * going to be affected by this query.
     *
     * @return @b Reference to array that holds table names in strings.
     */
    public function &getTableNames() {
        return $this->mTableNames;
    }

    /**
     * @brief Queries current query.
     *
     * Query the query that is returned by compile() function using
     * driver object returned by getDbDriver() function.
     *
     * @throws DbDriverException When query execution failed.
     * @return Result of \\cfd\\core\\DbDriver::query() function.
     */
    public function send() {
        // we will compile only if it is needed
        if( $this->mWasChanged || is_null($this->mLastCompileOutput) ) {
            $this->mLastCompileOutput = $this->compile();
        }
        return $this->getDbDriver()->query($this->mLastCompileOutput);
    }

    /**
     * @brief Compiles query.
     *
     * This function translate query specified by object's properties
     * to query string that is suitable for database system of query object.
     *
     * @return @b String that corresponds to query that is understood
     * by database system.
     */
    abstract public function compile();

    /**
     * @brief Indicates that query has to be compiled.
     *
     * If you call this function query will be compiled during
     * next call of send() function. You has to call it in every
     * function that affectes variables used during compilation in
     * compile() function. It should be called before return statement
     * or before end of function if there is not return statement.
     */
    public function enforceCompilation() {
        $this->mWasChanged = true;
    }

}
