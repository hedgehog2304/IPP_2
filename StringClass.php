<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file StringClass.php
 * @brief Built-in String class with its built-in methods
 * @date 22.04.2025
 */

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Core\Settings;

class StringClass extends ObjectClass
{
    /** @var string */
    public mixed $value;

    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    /**
     * @param ObjectClass[] $args
     */
    public function from(array $args): StringClass
    {
        if ($args[0] instanceof StringClass) {
            return new StringClass($args[0]->value);
        } else {
            fwrite(STDERR, "Error: Expected StringClass\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }


    public function new(): StringClass
    {
        return new StringClass();
    }

    public function read(): StringClass
    {
        $settings = new Settings();
        $inputReader = $settings->getInputReader();
        return new StringClass((string)$inputReader->readString());
    }

    public function print(): mixed
    {
        echo $this->value;
        return null;
    }

    /**
     * @param ObjectClass[] $args
     */
    public function equalTo(array $args): mixed
    {
        if ($args[0] instanceof StringClass) {
            if ($this->value === $args[0]->value) {
                return new TrueClass();
            } else {
                return new FalseClass();
            }
        } else {
            fwrite(STDERR, "Error: Only string == string\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }

    public function asString(): StringClass
    {
        return new StringClass($this->value);
    }

    public function asInteger(): mixed
    {
        if (is_numeric($this->value) && (string)(int)$this->value === (string)$this->value) {
            return new IntegerClass((int)$this->value);
        }
        return new NilClass();
    }

    /**
     * @param ObjectClass[] $args
     */
    public function concatenateWith(array $args): mixed
    {
        if ($args[0] instanceof StringClass) {
            return new StringClass($this->value . $args[0]->value);
        } else {
            return new NilClass();
        }
    }

    /**
     * @param ObjectClass[] $args
     */
    public function startsWithEndsBefore(array $args): mixed
    {
        if ($args[0] instanceof IntegerClass && $args[1] instanceof IntegerClass) {
            $start = $args[0]->value;
            $end = $args[1]->value;

            if ($start <= 0 || $end <= 0) {
                return new NilClass();
            }

            if ($end <= $start) {
                return new StringClass();
            } else {
                return new StringClass(substr($this->value, $start - 1, $end - $start));
            }
        } else {
            fwrite(STDERR, "Error: Only int values must be as arguments\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }
    }
}
