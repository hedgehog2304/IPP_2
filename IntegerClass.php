<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file IntegerClass.php
 * @brief Built-in Integer class with its built-in methods
 * @date 22.04.2025
 */

namespace IPP\Student;

use IPP\Core\ReturnCode;

class IntegerClass extends ObjectClass
{
    /** @var int */
    public mixed $value;

    public function __construct(int $value = 0)
    {
        $this->value = $value;
    }

    public function new(): IntegerClass
    {
        return new IntegerClass();
    }

    /**
     * @param ObjectClass[] $args
     */
    public function from(array $args): IntegerClass
    {
        if ($args[0] instanceof IntegerClass) {
            return new IntegerClass($args[0]->value);
        } else {
            fwrite(STDERR, "Error: from: expected IntegerClass\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * @param ObjectClass[] $args
     */
    public function equalTo(array $args): mixed
    {
        if ($args[0] instanceof IntegerClass) {
            if ($this->value === $args[0]->value) {
                return new TrueClass();
            } else {
                return new FalseClass();
            }
        } else {
            fwrite(STDERR, "Error: Only int == int\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * @param ObjectClass[] $args
     */
    public function greaterThan(array $args): mixed
    {
        if ($args[0] instanceof IntegerClass) {
            $result = $this->value > $args[0]->value;
            if ($result) {
                return new TrueClass();
            } else {
                return new FalseClass();
            }
        } else {
            fwrite(STDERR, "Error: Only int > int\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * @param ObjectClass[] $args
     */
    public function plus(array $args): mixed
    {
        if ($args[0] instanceof IntegerClass) {
            return new IntegerClass($this->value + $args[0]->value);
        } else {
            fwrite(STDERR, "Error: Only int + int\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * @param ObjectClass[] $args
     */
    public function minus(array $args): mixed
    {
        if ($args[0] instanceof IntegerClass) {
            return new IntegerClass($this->value - $args[0]->value);
        } else {
            fwrite(STDERR, "Error: Only int - int\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * @param ObjectClass[] $args
     */
    public function multiplyBy(array $args): mixed
    {
        if ($args[0] instanceof IntegerClass) {
            return new IntegerClass($this->value * $args[0]->value);
        } else {
            fwrite(STDERR, "Error: Only int * int\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * @param ObjectClass[] $args
     */
    public function divBy(array $args): mixed
    {
        if ($args[0] instanceof IntegerClass) {
            if ($args[0]->value == 0) {
                fwrite(STDERR, "Error: Dividing by 0\n");
                exit(ReturnCode::INTERPRET_VALUE_ERROR);
            } else {
                return new IntegerClass(intdiv($this->value, $args[0]->value));
            }
        } else {
            fwrite(STDERR, "Error: Only int / int\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    public function asString(): StringClass
    {
        return new StringClass((string) $this->value);
    }

    public function asInteger(): IntegerClass
    {
        return new IntegerClass($this->value);
    }

    /**
     * @param ObjectClass[] $args
     */
    public function timesRepeat(array $args): mixed
    {
        if ($this->value <= 0) {
            fwrite(STDERR, "Error: int must be > 0\n");
            exit(ReturnCode::INTERPRET_VALUE_ERROR);
        } else {
            if ($args[0] instanceof BlockClass) {
                for ($i = 1; $i <= $this->value; $i++) {
                    $args[0]->value(array(new IntegerClass($i)));
                }
                return new NilClass();
            } else {
                fwrite(STDERR, "Error: Arg must be a block\n");
                exit(ReturnCode::INTERPRET_TYPE_ERROR);
            }
        }
    }
}
