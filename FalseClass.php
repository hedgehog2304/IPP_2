<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file FalseClass.php
 * @brief Built-in False class with its built-in methods
 * @date 22.04.2025
 */

namespace IPP\Student;

use IPP\Core\ReturnCode;

class FalseClass extends ObjectClass
{
    /** @var bool */
    public mixed $value;

    public function __construct(bool $value = false)
    {
        $this->value = $value;
    }

    /**
     * @param ObjectClass[] $args
     */
    public function from(array $args): FalseClass
    {
        return new FalseClass((bool)$args[0]->value);
    }

    public function new(): FalseClass
    {
        return new FalseClass();
    }

    public function not(): TrueClass
    {
        return new TrueClass();
    }

    public function and(): FalseClass
    {
        return new FalseClass();
    }

    /**
     * @param ObjectClass[] $args
     */
    public function or(array $args): mixed
    {
        if ($args[0] instanceof BlockClass) {
            if ($args[0]->value() instanceof TrueClass) {
                return new TrueClass();
            } else {
                return new FalseClass();
            }
        } else {
            fwrite(STDERR, "Error: Arg of or: must be a block\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    /**
     * @param ObjectClass[] $args
     */
    public function ifTrueifFalse(array $args): mixed
    {
        if ($args[1] instanceof BlockClass) {
            return $args[1]->value();
        } else {
            fwrite(STDERR, "Error: Args of ifTrue:ifFalse: must be blocks\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
