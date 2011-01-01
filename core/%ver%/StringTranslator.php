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
 * @brief Functionality that translates strings.
 *
 * This functionality interface is usrable for modules
 * that are suppose to translate strings. Each module
 * that wants to do string translations has to implement
 * this interface.
 *
 * Note that only \\cfd\\core\\Object subclasses can implement
 * this interface.
 *
 * @see \\cfd\\core\\ModuleFunctionality, \\cfd\\core\\I18n
 */
interface StringTranslator extends ModuleFunctionality {
    /**
     * @brief Function that translate string.
     *
     * This function has to be implemented in class that
     * implements this interface. It has to return desired
     * string translation or if it's not possible set $succed
     * to false.
     *
     * @param string $domainName Domain that should be look for translation.
     * @param string $strsLocale Language in which are $strs written. If translation
     * is about to translate strings to this language $succed has to be set to false.
     * @param mixed $strs String contains singular form of string that is going to be
     * translated or array that contains singular form and all plural forms of string
     * that is going to be translated.
     * @param integer $n Number that is used to determine which plural form should be
     * used. Put this number to plural expression.
     * @param boolean $succed Variable that has to be set to false if string translation
     * was not successful.
     * @return String translation if translation was successful, otherwise $succed is
     * set to false and return variable is pointless.
     */
    public function translateString($domainName, $strsLocale, $strs, $n, &$succed);
}
