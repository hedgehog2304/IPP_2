<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file TrueClass.php
 * @brief Built-in True class with its built-in methods
 * @date 22.04.2025
 */

namespace IPP\Student;

use IPP\Core\ReturnCode;

class TrueClass extends ObjectClass
{
    /** @var bool */
    public mixed $value;

    public function __construct(bool $value = true)
    {
        $this->value = $value;
    }

    /**
     * @param ObjectClass[] $args
     */
    public function from(array $args): TrueClass
    {
        return new TrueClass((bool)$args[0]->value);
    }

    public function new(): TrueClass
    {
        return new TrueClass();
    }

    public function not(): FalseClass
    {
        return new FalseClass();
    }

    /**
     * @param ObjectClass[] $args
     */
    public function and(array $args): mixed
    {
        if ($args[0] instanceof BlockClass) {
            if ($args[0]->value() instanceof TrueClass) {
                return new TrueClass();
            } else {
                return new FalseClass();
            }
        } else {
            fwrite(STDERR, "Error: Arg of and: must be a block\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    public function or(): TrueClass
    {
        return new TrueClass();
    }

    /**
     * @param ObjectClass[] $args
     */
    public function ifTrueifFalse(array $args): mixed
    {
        if ($args[0] instanceof BlockClass) {
            return $args[0]->value();
        } else {
            fwrite(STDERR, "Error: Args of ifTrue:ifFalse: must be blocks\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
