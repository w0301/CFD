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
 * Subclass this class in your own module in this way:
 * @code
 *  namespace cfd\modules\MyModule;
 *
 *  class I18n extends \cfd\core\I18n {
 *  	// if you want you can change language settings by
 *  	// overriding these functions
 *  	public static function getLiteralsLocale() {
 *  		return "sk";
 *  	}
 *  	public static function getPluralsExpression() {
 *  		return "nplurals=3; plural=...;";	// replace '...' with right expression
 *  	}
 *		public static function getStringsDomain() {
 *       	return "MyModule";		// returns domain name for this module - should be
 *       							// same as module name
 *   	}
 *
 *  }
 * @endcode
 * When you do this subclass you can translate literals in your
 * module's code by calling I18n::tr() function. And this call will
 * translate your strings using the domain that you chose in getStringsDomain() function.
 *
 * To allow static function overrides there is always used keyword
 * @b static instead of @b self in all functions of this class.
 *
 * @see tr(), $sTranslateString
 */
class I18n {
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
     * if there is any plural form. The function has to return translation
     * of string on succes or when succes is not achieved it has to set $succed
     * variable to false.
     *
     * Prototype of function that can be connected to this
     * signal should looks like this:
     * @code
     * 	function func($domainName, $strsLocale, $strs, $n, &$succed);
     * @endcode
     *
     * @see \\cfd\\core\\ConditionalSignal
     */
    protected static $sTranslateString;

    /**
     * @brief Name of literal's locale.
     *
     * Override this function in your module to
     * change locale for your module's literals.
     *
     * @return Name of locale that's language is
     * used for this I18n class. For core's I18n
     * class returns "en";
     */
    public static function getLiteralsLocale() {
    	return "en";
    }

    /**
     * @brief Expression for plural forms.
     *
     * Override this function to return your own expression.
     * Do this only when you want override getLiteralsLocale()
     * as well.
     *
     * @return Expression in string that represent rule for
     * plural form determination. For core's I18n class this
     * returns "nplurals=2; plural=n != 1;".
     */
    public static function getPluralsExpression() {
    	return "nplurals=2; plural=n != 1;";
    }

    /**
     * @brief Domain for strings.
     *
     * Override this function to use different domain.
     *
     * @return Name of domain that should be looked for
     * string translations. For core I18n class this
     * is always empty string ("").
     */
    public static function getStringsDomain() {
        return "";
    }

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
        static $called = false;
        if($called === true) return;
        $called = true;

        // create signal that's emit returns translated string
        self::$sTranslateString = new ConditionalSignal();
    }

    /**
     * @brief Adds new string translator.
     *
     * This function simple connects function that translates
     * strings to $sTranslateString signal.
     *
     * @param StringTranslator $translator Translator object that's function will
     * be connected to $sTranslateString signal.
     * * @see addTranslator(), $sTranslateString
     */
    public static function addTranslator(StringTranslator $translator) {
        self::$sTranslateString->connect( array($translator, "translateString") );
    }

    /**
     * @brief Remove string translator.
     *
     * This function simple disconnects function that translates
     * string from $sTranslateString signal.
     *
     * @param StringTranslator $translator Translator object that's function
     * will be disconnected from $sTranslateString signal.
     * @see addTranslator(), $sTranslateString
     */
    public static function removeTranslator(StringTranslator $translator) {
        self::$sTranslateString->disconnect( array($translator, "translateString") );
    }

    /**
     * This function emits $sTranslateString signal and if emit
     * was successful returns the translated string. Override
     * this only if you want change the way how is this signal emited.
     *
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
    protected static function translate($domainName, $strs, $n = 1) {
        $retStr = self::$sTranslateString->emit($domainName, static::getLiteralsLocale(), $strs, $n);
        if( !self::$sTranslateString->wasLastEmitSuccessful() ) {
            if( is_array($strs) ) {
                $exp = new ExpressionEvaluator( static::getPluralsExpression() );
                $exp->setVariable("n", $n);
                $exp->evaluate();
                return $strs[$exp->getVariable("plural")];
            }
            return $strs;
        }
        return $retStr;
    }

    /**
     * @brief Shortcut for translate() function.
     *
     * This function calls function translate() with domain name
     * returned by getStringsDomain() function. Override that function
     * if you want change domain name. This function is marked as final
     * so it can't be overrided if you want change any behaviour
     * of string translating override getStringsDomain() or translate() function.
     * However this function adds one extre feature to translate() function. It allows
     * using variable names in translated string. Each variable has to start with one of
     * the folowing chars:
     * @code
     *  @ - if you want to apply htmlspecialchars() filter on variable's value
     *  ! - if you don't want to apply any filter
     * @endcode
     *
     * Note that this is static function.
     *
     * @param mixes $strs Singular form in string or singular form + all plural
     * forms in array.
     * @param array $vars Array of variables that should be substituted after string
     * translation. It has to contain variable name as key (including '@' or '!' char
     * at the begining of name) and variable's value as key's value (this value can be
     * also a PHP variable). Leave empty if there are no variables.
     * @param integer $n Number that is used to determine if singular or plural
     * form should be used. This number is simply quantity that is used in string.
     * @return Return value of translate() function with substituted variables.
     * @see translate(), getStringsDomain(), $sTranslateString
     */
    public final static function tr($strs, $vars = array(), $n = 1) {
        $retStr = static::translate(static::getStringsDomain(), $strs, $n);

        // filtering variable's values
        if( is_null($vars) ) $vars = array();
        foreach($vars as $key => $val) {
            switch($key[0]) {
                case '@':
                    // apply PHP's htmlspecialchars() function
                    $vars[$key] = htmlspecialchars($val, ENT_QUOTES, "UTF-8");
                    break;
                case '!':
                    // do nothing
                    break;
                default:
                    // if filetering level is not specified delete variable
                    unset($vars[$key]);
                    break;
            }
        }

        // we will use PHP's function to supstitude
        return strtr($retStr, $vars);
    }

} I18n::__static();
