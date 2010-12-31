<?php
/*
 * Copyright (C) 2010 Richard Kakaš.
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
 * @brief This class is used for translating strings.
 *
 * This class provide i18n functions and variables. It
 * contains functions and signals that are used to send
 * user visible strings that are about to translate to
 * translators objects. Note that this class or its objects
 * do not provide any info about user's language or locale.
 * This informations are determined in translators objects.
 *
 * @see tr(), $sTranslateString
 */
class I18n extends Object {
    /**
     * @brief Connection point for strings translators.
     *
     * This signal is used as connection point for all
     * strings translators functions. This signal is of
     * type ConditionalSignal and it sends 4 arguments -
     * domain of string's translation, string's locale (respectively language),
     * string that contains singular form or array that contains singular
     * form + all plural forms and last argument is number that shows quantity that
     * is used to determine if plural or singular form should be used.
     * Domain is empty string if default domain should be used (note that
     * each module should have own domain), second argument is array only
     * if there is any plural form.
     *
     * Prototype of function that can be connected to this
     * signal should looks like this:
     * @code
     * 	function func($domainName, $strsLocale, $strs, $n, &$succed);
     * @endcode
     *
     * @see \\cfd\\core\\ConditionalSignal
     */
    public static $sTranslateString;

    /**
     * Holds name of locale that's language is
     * used for this I18n class. Define own variable
     * in module's I18n class if you want to use any
     * other locale.
     */
    public static $sStringsLocale = "en";

    /**
     * Holds information about how are plural forms determined
     * for default string's literals locale. This rule is always
     * valid for locale stored in $sStringsLocale variable.
     */
    public static $sStringsPlurals = "nplurals=2; plural=n != 1;";

    /**
     * Creates new object.
     * @param object $parent Parent of new object.
     */
    public function __construct($parent = NULL) {
        parent::__construct($parent);
    }

    /**
     * Destroys object.
     */
    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Does static initialization of object. Never
     * call this function, it's called automatically!
     */
    public static function __static() {
        // create signal that's emit returns translated string
        self::$sTranslateString = new ConditionalSignal();
    }

    /**
     * This function emits $sTranslateString signal and if emit
     * was successful returns the translated string.
     * Note that this is static function.
     *
     * @param string $domainName Name of domain that will be looked up for
     * string translation.
     * @param mixed $strs String containing singular form or array containg
     * singular form plus all plural form (2. option only string has plural form).
     * @param integer $n Number that is used to determine if plural form
     * or singular form shoul be used. This number usually contains the quantity
     * that is used in the string. This quantity is then used in translator to
     * determine if singular or plural form should be used.
     * @return Correctly translated string if such was returned by connected
     * functions or untranslated string if translation was not found (if $n == 1 singular
     * form, otherwise plural form - index 1 in array)
     * @see tr(), $sTranslateString
     */
    public static function translate($domainName, $strs, $n = 1) {
        $retStr = self::$sTranslateString->emit($domainName, static::$sStringsLocale, $strs, $n);
        if( !self::$sTranslateString->wasLastEmitSuccessful() ) {
            return is_array($strs) ? ($n != 1 ? $strs[1] : $strs[0]) : $strs;
        }
        return $retStr;
    }

    /**
     * @brief Shortcut for translate() function.
     *
     * This function calls function translate() with empty
     * domain name. Override this function in module's I18n
     * class to use module's domain easly.
     * Note that this is static function.
     *
     * @param string $strs Singular form in string or singular form plural
     * form/s in array.
     * @param integer $n Number that is used to determine if singular or plural
     * form should be used.
     * @return Return value of translate() function.
     * @see translate(), $sTranslateString
     */
    public static function tr($strs, $n = 1) {
        return self::translate("", $strs, $n);
    }

} I18n::__static();
