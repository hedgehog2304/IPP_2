<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file NilClass.php
 * @brief Built-in Nil class with its built-in methods
 * @date 22.04.2025
 */

namespace IPP\Student;

class NilClass extends ObjectClass
{
    /** @var mixed */
    public mixed $value;

    public function __construct(mixed $value = 'nil')
    {
        $this->value = $value;
    }

    /**
     * @param ObjectClass[] $args
     */
    public function from(array $args): NilClass
    {
        return new NilClass($args[0]->value);
    }

    public function new(): NilClass
    {
        return new NilClass();
    }

    public function asString(): StringClass
    {
        return new StringClass('nil');
    }
}
