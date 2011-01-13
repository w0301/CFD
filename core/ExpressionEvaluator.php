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
 * @brief Evaluates expressions in strings.
 *
 * Objects of this class can evaluate expressions
 * that are represented by string. These expressions
 * have C-like form, for example:
 * @code
 * 	n = 10; x = (n == 10) ? 78 + 12 : 83 * 2 + 123 / 123;
 * @endcode
 * Expression parser tolerates operator precedence (just like we
 * do in math). Each given expression has to contains variables
 * that have an assigned value. These variables can be used in
 * later-defined variables assignment. You can get value of desired
 * variable value by getVariable() function.
 *
 * Use this class to evaluate your plural forms expressions in your
 * own string translators.
 *
 * @see \\cfd\\core\\I18n
 */
class ExpressionEvaluator extends Object {
    private static $sDefaultOperators = NULL;
    private $mExpressionStrings = array();
    private $mExpressionStringI = array();
    private $mOperators = NULL;
    private $mVariables = array();

    private function isOperatorDefined($opName) {
        if( array_key_exists($opName, $this->mOperators) ) {
            return true;
        }
        return false;
    }

    private function &getOperatorProperties($opName) {
        if( !$this->isOperatorDefined($opName) ) {
            throw new ExpressionException( I18n::tr("Operator '$opName' is not defined.") );
        }
        return $this->mOperators[$opName];
    }

    private function getNextNonSpaceChar($i) {
        // $i - index of expression instruction
        if( $i >= count($this->mExpressionStrings) ||
            $this->mExpressionStringI[$i] >= strlen($this->mExpressionStrings[$i]) ) {
            return false;
        }
        $ch = $this->mExpressionStrings[$i][ $this->mExpressionStringI[$i] ];
        $this->mExpressionStringI[$i]++;
        if( ctype_space($ch) ) {
            if( $this->mExpressionStringI[$i] < strlen($this->mExpressionStrings[$i]) ) {
                return $this->getNextNonSpaceChar($i);
            }
            return false;
        }
        return $ch;
    }

    private function getNextChar($i) {
        // $i - index of expression instruction
        if( $i >= count($this->mExpressionStrings) ||
            $this->mExpressionStringI[$i] >= strlen($this->mExpressionStrings[$i]) ) {
            return false;
        }
        $ch = $this->mExpressionStrings[$i][ $this->mExpressionStringI[$i] ];
        $this->mExpressionStringI[$i]++;
        if( ctype_space($ch) && $ch != " " ) {
            if( $this->mExpressionStringI[$i] < strlen($this->mExpressionStrings[$i]) ) {
                return $this->getNextChar($i);
            }
            return false;
        }
        return $ch;
    }

    private function putCharBack($i) {
        $this->mExpressionStringI[$i]--;
    }

    private function getNextToken($i) {
        // $i - index of expression instruction
        $ch = $this->getNextNonSpaceChar($i);
        if($ch === false) return false;
        if( ctype_digit($ch) ) {
            // token is constant integer => find the rest of number
            $numVal = $ch;
            while(1) {
                $ch = $this->getNextChar($i);
                if($ch === false) {
                    break;
                }
                else if( ctype_digit($ch) ) {
                    $numVal .= $ch;
                }
                else if($ch == ".") {
                    $decPart = $this->getNextToken($i);
                    if($decPart !== false && $decPart["type"] == 1) {
                        $numVal .= "." . $decPart["val"];
                        return array("type" => 1, "val" => (float) $numVal);
                    }
                    return false;
                }
                else {
                    // we have to put char back to expression instruction
                    // because it's part of next token
                    $this->putCharBack($i);
                    break;
                }
            }
            return array("type" => 1, "val" => (integer) $numVal);
        }
        else if( ctype_alpha($ch) ) {
            // token is varaible name => find the rest (also can contains number)
            $varName = $ch;
            while(1) {
                $ch = $this->getNextChar($i);
                if($ch === false) {
                    break;
                }
                else if( ctype_digit($ch) || ctype_alpha($ch) ) {
                    $varName .= $ch;
                }
                else {
                    // we have to put char back to expression instruction
                    // because it's part of next token
                    $this->putCharBack($i);
                    break;
                }
            }
            return array("type" => 2, "val" => $varName);
        }
        else if( ctype_punct($ch) ) {
            // token is operator (operators is allowd to contains only puct characters +
            // these characters are not allowed in other tokens)
            $opName = $ch;
            // adding support for number that are less than zero
            if( $opName == "-" && ctype_alnum($this->getNextChar($i)) ) {
                $this->putCharBack($i);
                $arr = $this->getNextToken($i);
                $arr["val"] = $arr["val"] * -1;
                return $arr;
            }
            while(1) {
                if( $this->isOperatorDefined($opName) || $opName == "(" || $opName == ")") break;
                $ch = $this->getNextChar($i);
                if($ch === false) {
                    break;
                }
                else if( ctype_punct($ch) ) {
                    $opName .= $ch;
                }
                else {
                    // we have to put char back to expression instruction
                    // because it's part of next token
                    $this->putCharBack($i);
                    break;
                }
            }
            return array("type" => 3, "val" => $opName);
        }
        return false;
    }

    /**
     * @brief Static initialization.
     *
     * This functio does static init for class.
     * Never call this function.
     */
    public static function __static() {
        if( !is_null(self:: $sDefaultOperators) ) return;
        self::$sDefaultOperators = array(
            // index is name of operator, precedence, associativity, canTakeVarNames and func -
            // always take 3 arguments - left parameter, right parameter and evaluator's reference (if needed)
            "*" => array("precedence" => 90, "associativity" => "L", "onlyConstants" => true,
                         "func" => create_function('$l, $r', 'return $l * $r;')
                        ),
            "/" => array("precedence" => 90, "associativity" => "L", "onlyConstants" => true,
                         "func" => create_function('$l, $r', 'return $l / $r;')
                        ),
            "+" => array("precedence" => 80, "associativity" => "L", "onlyConstants" => true,
                         "func" => create_function('$l, $r', 'return $l + $r;')
                        ),
            "-" => array("precedence" => 80, "associativity" => "L", "onlyConstants" => true,
                         "func" => create_function('$l, $r', 'return $l - $r;')
                        ),
            "=" => array("precedence" => 0, "associativity" => "L", "onlyConstants" => false,
                         "func" => create_function('$l, $r, $ref', '
                         	if($l["type"] != 2) {
								throw new cfd\core\ExpressionException(
    								cfd\core\I18n::tr("Left operand for \'=\' operator has to be a variable and not.")
    							);
    						}
                         	if($r["type"] == 2) $r["val"] = $ref->getVariable($r["val"]);
                         	$ref->setVariable($l["val"], $r["val"]);
                         	return $r["val"];')
                        )
        );
    }

    /**
     * @brief Creates new object.
     *
     * This constructor creates new object with given
     * parent and given set of operators for evaluator.
     *
     * @param string $exp String that contains expression to
     * be evaluate after calling evaluate() function.
     * @param array $operators Array that describes operators
     * that are avaible to evaluator. If this us NULL default
     * array is used.
     * @param object $parent Parent of new object.
     */
    public function __construct($exp = "", $operators = NULL, $parent = NULL) {
        parent::__construct($parent);
        $this->setExpression($exp);
        if( is_null($operators) ) {
            $this->mOperators =& self::$sDefaultOperators;
        }
    }

    /**
     * @brief Sets evaluator's expression.
     *
     * This function sets expression that will be evaluated
     * by evaluate() function. You can call this function to
     * reset expression as many times as you want.
     *
     * @param string $expStr String that describes expression.
     * @see evaluate(), getExpression()
     */
    public function setExpression($expStr) {
        $this->mExpressionStrings = explode(";", $expStr);
        // setting index for every expression part
        $this->mExpressionStringI = array_fill(0, count($this->mExpressionStrings), 0);
    }

    /**
     * @brief Returns object's expression.
     *
     * @return Expression string that was previously set by
     * setExpression() function.
     * @see setExpression()
     */
    public function getExpression() {
        return implode(";", $this->mExpressionStrings);
    }

    /**
     * @brief Sets or creates varaible.
     *
     * This function sets variable's value. If variable with
     * given name does not exist it firstly creates the variable.
     *
     * @param string $name Name of variable to be set.
     * @param integer $value Value that will be assigned. Only
     * integer values are supported.
     * @see getVariable()
     */
    public function setVariable($name, $value) {
        if( !ctype_alpha($name[0]) ) {
            throw new ExpressionException( I18n::tr("Variable name has to start with a letter.") );
        }
        if( !is_numeric($value) ) {
            throw new ExpressionException( I18n::tr("Variable value has to be a number.") );
        }
        $this->mVariables[$name] = $value;
    }

    /**
     * @brief Checks variable existence.
     *
     * @return @b True if variable exists, @b false otherwise.
     * @param string $name Name of variable.
     */
    public function isVariableDefined($name) {
        return array_key_exists($name, $this->mVariables);
    }

    /**
     * @brief Returns variables value.
     *
     * @param string $name Name of variable that's value
     * will be returned.
     * @return @b False if variable does not exist or its integer
     * value if it exists.
     * @see setVariable()
     */
    public function getVariable($name) {
        if( !$this->isVariableDefined($name) ) {
            return false;
        }
        return $this->mVariables[$name];
    }

    /**
     * @brief Evaluates expression.
     *
     * This function start evaluation of expression
     * that was given to setExpression() function or to object's
     * constructor. After this function is called you can get values
     * of all variables that have been set in expression. Use getVariable()
     * to do that. During evaluation all variables set by setVariable() function
     * are present so they can be used in expression without initialization.
     *
     * @throws ExpressionException If there is any syntax error in expression.
     * @see setVariable(), getVariable()
     */
    public function evaluate() {
        $postfixArr = array();

        // cycle that will go threw all instructions
        // and transoform them to postfix format
        foreach($this->mExpressionStrings as $key => &$val) {
            $output = array();    // holds arrays - "type". index - 1 = const, 2 = var name, 3 = oper;
                                  // "val". index - value, name if var or operator
            $operators = array();    // will contain only indexes of operators

            // go threw all tokens
            $token = array();
            while( ($token = $this->getNextToken($key)) !== false ) {
                if($token["type"] == 1 || $token["type"] == 2) {
                    // put it right to the output
                    $output[] = $token;
                }
                else if( array_key_exists($token["val"], $this->mOperators) || $token["val"] == "(" ||
                    $token["val"] == ")") {
                    // it's operator => we have to process it specially
                    if($token["val"] == "(") {
                        $operators[] = $token;
                    }
                    else if($token["val"] == ")") {
                        while(1) {
                            $last = array_pop($operators);
                            if( is_null($last) ) {
                                throw new ExpressionException( I18n::tr("Parenthesis '(' is missing in expression.") );
                            }
                            if($last["val"] == "(") break;
                            $output[] = $last;
                        }
                    }
                    else {
                        $lastOp = end($operators);
                        if($lastOp !== false && $lastOp["val"] != "(" && $lastOp["val"] != ")") {
                            $lastOpProp =& $this->getOperatorProperties($lastOp["val"]);
                            $tokenOpProp =& $this->getOperatorProperties($token["val"]);
                            while( ($tokenOpProp["associativity"] == "L" &&
                                    $tokenOpProp["precedence"] <= $lastOpProp["precedence"]) ||
                                   ($tokenOpProp["associativity"] == "R" &&
                                    $tokenOpProp["precedence"] < $lastOpProp["precedence"]) ) {
                                $output[] = array_pop($operators);
                                $lastOp = end($operators);
                                if($lastOp !== false && $lastOp["val"] != "(") {
                                    $lastOpProp =& $this->getOperatorProperties($lastOp["val"]);
                                }
                                else {
                                    break;
                                }
                            }
                        }
                        $operators[] = $token;
                    }
                }
            }
            while( ($op = array_pop($operators)) !== NULL ) {
                if($op["val"] == "(") {
                    throw new ExpressionException( I18n::tr("Parenthesis ')' is missing in expression.") );
                }
                $output[] = $op;
            }

            // evaluate postfix notation in $output
            $stack = array();
            while( ($token = array_shift($output)) !== NULL ) {
                if($token["type"] == 1 || $token["type"] == 2) {
                    // it's variable or number
                    $stack[] = $token;
                }
                else {
                    // it's operator
                    $stackSize = count($stack);
                    $opProp =& $this->getOperatorProperties($token["val"]);
                    $func = $opProp["func"];
                    $rightParam = 0;
                    $leftParam = 0;
                    if($stackSize >= 2) {
                        $rightParam = array_pop($stack);
                        $leftParam = array_pop($stack);
                    }
                    else {
                        throw new ExpressionException( I18n::tr("Each operator requires 2 operands.") );
                    }
                    if($opProp["onlyConstants"]) {
                        if($leftParam["type"] == 2) {
                            $varName = $leftParam["val"];
                            if( !$this->isVariableDefined($varName) ) {
                                throw new ExpressionException( I18n::tr("Variable '$varName' does not exist.") );
                            }
                            $leftParam["val"] = $this->getVariable($leftParam["val"]);
                        }
                        if( $rightParam["type"] == 2) {
                            $varName = $rightParam["val"];
                            if( !$this->isVariableDefined($varName) ) {
                                throw new ExpressionException( I18n::tr("Variable '$varName' does not exist.") );
                            }
                            $rightParam["val"] = $this->getVariable($rightParam["val"]);
                        }
                        $leftParam = $leftParam["val"];
                        $rightParam = $rightParam["val"];
                    }
                    $stack[] = array( "type" => 1, "val" => $func($leftParam, $rightParam, $this) );
                }
            }
        }

        // we will set index for possible re-evaluation
        $this->mExpressionStringI = array_fill(0, count($this->mExpressionStrings), 0);
    }

} ExpressionEvaluator::__static();
