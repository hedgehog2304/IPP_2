<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file ObjectClass.php
 * @brief Built-in Object class with its built-in methods
 * @date 22.04.2025
 */

namespace IPP\Student;

class ObjectClass
{
    public mixed $value;

    public function hasMethod(string $selector): bool
    {
        return method_exists($this, str_replace(':', '', $selector));
    }

    /** @param ObjectClass[] $args */
    public function identicalTo(array $args): mixed
    {
        if ($this->value === $args[0]->value) {
            return new TrueClass();
        } else {
            return new FalseClass();
        }
    }

    /** @param ObjectClass[] $args */
    public function equalTo(array $args): mixed
    {
        if ($this->value === $args[0]->value) {
            return new TrueClass();
        } else {
            return new FalseClass();
        }
    }

    public function asString(): mixed
    {
        if ($this instanceof TrueClass) {
            return new StringClass('true');
        } elseif ($this instanceof FalseClass) {
            return new StringClass('false');
        } elseif ($this instanceof NilClass) {
            return new StringClass('nil');
        } else {
            return new StringClass();
        }
    }

    public function isNumber(): mixed
    {
        if ($this instanceof IntegerClass) {
            return new TrueClass();
        } else {
            return new FalseClass();
        }
    }

    public function isString(): mixed
    {
        if ($this instanceof StringClass) {
            return new TrueClass();
        } else {
            return new FalseClass();
        }
    }

    public function isBlock(): mixed
    {
        if ($this instanceof BlockClass) {
            return new TrueClass();
        } else {
            return new FalseClass();
        }
    }

    public function isNil(): mixed
    {
        if ($this instanceof NilClass) {
            return new TrueClass();
        } else {
            return new FalseClass();
        }
    }
}
