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
     * domain for string that is being translated, singular
     * form of string, plural form of string and number that
     * shows quantity that is used to determine if plural form
     * should be used. Domain is empty string if default domain
     * should be used (note that each module should have own domain),
     * plural form string is empty if there is no plural form for
     * string (in this case last argument is 1).
     *
     * Prototype of function that can be connected to this
     * signal should looks like this:
     * @code
     * 	function func($domainName, $singularStr, $pluralStr, $n, &$succed);
     * @endcode
     *
     * @see \\cfd\\core\\ConditionalSignal
     */
    public static $sTranslateString;

    /**
     * Holds name of locale that's language is
     * used to write all strings literal in CFD
     * source files. This is always "en"!
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
     * @param string $str1 Singular form of string that will be translated.
     * @param string $str2 Plural form of string that will be translated.
     * @param integer $n Number that is used to determine if plural form
     * or singular form shoul be used. This number usually contains the quantity
     * that is used in the string. This quantity is then used in translator to
     * determine if singular or plural form should be used.
     * @return Correctly translated string if such was returned by connected
     * functions or untranslated string if translation was not found (if $n == 1 singular
     * form, otherwise plural form)
     * @see tr(), $sTranslateString
     */
    public static function translate($domainName, $str1, $str2 = "", $n = 1) {
        $retStr = self::$sTranslateString->emit($domainName, $str1, $str2, $n);
        if( !self::$sTranslateString->wasLastEmitSuccessful() ) {
            return $n == 1 ? $str1 : $str2;
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
     * @param string $str1 Singular form of string.
     * @param string $str2 Plural form of string, or empty string if there is no
     * plural form.
     * @param integer $n Number that is used to determine if singular or plural
     * form should be used.
     * @return Return value of translate() function.
     * @see translate(), $sTranslateString
     */
    public static function tr($str1, $str2 = "", $n = 1) {
        return self::translate("", $str1, $str2, $n);
    }

} I18n::__static();
