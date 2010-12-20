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
 * @brief Sorts and chooses specific versions.
 *
 * This class is used to list and sort version direcotry in
 * specified directory. Names of subdirectories of specified
 * directory are used as version strings. This class is used
 * internaly by CFD to determine the right version of core classes
 * and modules.
 *
 * Version's strings are sorted by dividing them to sub-strings.
 * These sub-strings are divided by '.' in main string. Let's
 * call these strings major strings. After that each major string
 * is divided to sub-strings that are divided by '-' in major string.
 * After these divisions coresponding parts of two versions will be compare.
 * They are firstly compare as integers and if they are equal they are also
 * compare as string by @b strcmp function. If first version has more parts
 * than second version missing part is considered to be empty (empty array when
 * major part is missing or empty string when sub-major part is missing).
 *
 * This sorting will sort following versions:
 * @code
 * 7.23.2
 * 7.23
 * 6.1
 * 8.123
 * 89-ver1.1
 * 89.3
 * 90.1-ver2
 * 90.1
 * @endcode
 * in this way:
 * @code
 * 6.1
 * 7.23
 * 7.23.2
 * 8.123
 * 89.3
 * 89-ver1.1
 * 90.1
 * 90.1-ver2
 * @endcode
 */
class VersionChooser {
    private $mWorkingDir;
    private $mVersionsArr;
    private $mIsSorted = false;

    /**
     * Constructs new VersionChooser object that will
     * choose between version specifed under given directory.
     *
     * @param string $dir Path to directory that contains versions (one subdirectory = one version)
     */
    public function __construct($dir) {
        $this->mWorkingDir = $dir . ($dir[strlen($dir) - 1] != "/" ? "/" : "");
        $this->mVersionsArr = array();

        $dirLister = opendir($this->mWorkingDir);
        while( ($filename = readdir($dirLister)) !== false ) {
            if( $filename != "." && $filename != ".." && is_dir($this->mWorkingDir . $filename) ) {
                $this->mVersionsArr[] = $filename;
            }
        }
        closedir($dirLister);
        if(count($this->mVersionsArr) == 1) $this->mIsSorted = true;
    }

    /**
     * @return @b True if this object operates at least on one version string, @b false otherwise.
     */
    public function hasAnyVersion() {
        return count($this->mVersionsArr) > 0;
    }

    /**
     * Returns all version strings in array sorted from
     * oldest versions to newest versions.
     */
    public function getVersions() {
        $this->sortVersions();
        return $this->mVersionsArr;
    }

    public function getOldestVersion() {
        $this->sortVersions();
        return reset($this->mVersionsArr);
    }

    public function getNewestVersion() {
        $this->sortVersions();
        return end($this->mVersionsArr);
    }

    /**
     * Returns path to oldest version's directory.
     */
    public function getOldestVersionPath() {
        if( !$this->hasAnyVersion() ) return "";
        return $this->mWorkingDir . $this->getOldestVersion() . "/";
    }

    /**
     * Returns path to newest version's directory.
     */
    public function getNewestVersionPath() {
        if( !$this->hasAnyVersion() ) return "";
        return $this->mWorkingDir . $this->getNewestVersion() . "/";
    }

    /**
     * Returns path to specific version's directory.
     * If specified version does not exist empty string is returned.
     *
     * @param string $ver Specific version's string.
     */
    public function getSpecificVersionPath($ver) {
        if(array_search($ver, $this->mVersionsArr) === false) return "";
        return $this->mWorkingDir . $ver . "/";
    }

    private function cmpVersions($l, $r) {
        if($l == $r) return 0;
        $lMajorityArr = explode(".", $l);
        $rMajorityArr = explode(".", $r);
        $lMajorityArrSize = count($lMajorityArr);
        $rMajorityArrSize = count($rMajorityArr);
        $majorityCount = max($lMajorityArrSize, $rMajorityArrSize);
        for($i = 0; $i != $majorityCount; $i++) {
            $lSubMajorityArr = current($lMajorityArr) !== false ? explode("-", current(each($lMajorityArr))) : array();
            $rSubMajorityArr = current($rMajorityArr) !== false ? explode("-", current(each($rMajorityArr))) : array();
            $lSubMajorityArrSize = count($lSubMajorityArr);
            $rSubMajorityArrSize = count($rSubMajorityArr);
            $subMajorityCount = max($lSubMajorityArrSize, $rSubMajorityArrSize);
            for($j = 0; $j != $subMajorityCount; $j++) {
                $lStr = current($lSubMajorityArr) !== false ? current(each($lSubMajorityArr)) : "";
                $rStr = current($rSubMajorityArr) !== false ? current(each($rSubMajorityArr)) : "";
                $lStrI = intval($lStr);
                $rStrI = intval($rStr);
                if($lStrI < $rStrI) {
                    return -1;
                }
                else if($lStrI > $rStrI) {
                    return 1;
                }
                else if($lStrI == 0 && $rStrI == 0) {
                    $strCmpRet = strcmp($lStr, $rStr);
                    if($strCmpRet != 0) {
                        return $strCmpRet;
                    }
                }
            }
        }
        return 0;
    }

    private function sortVersions() {
        if($this->mIsSorted == true) return;
        $this->mIsSorted = true;
        usort($this->mVersionsArr, "\cfd\core\VersionChooser::cmpVersions");
    }
}
