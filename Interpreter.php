<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file Interpreter.php
 * @brief Class, where the processing of the XML file starts
 * @date 22.04.2025
 */

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\ReturnCode;

class Interpreter extends AbstractInterpreter
{
    public function execute(): int
    {
        $dom = $this->source->getDOMDocument();
        $program = $dom->documentElement;

        if ($program === null) {
            fwrite(STDERR, "Error: Empty program\n");
            exit(ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
        }

        // Gets all classes from XML
        $builtinClasses = new BuiltInClasses($dom);
        $mainClass = $builtinClasses->findClassDOM($program, 'Main');

        if ($mainClass === null) {
            fwrite(STDERR, "Error: No Main Class\n");
            exit(ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
        }

        // Finds the run method
        $runMethod = $builtinClasses->findMethodDOM($mainClass, 'run');

        if ($runMethod === null) {
            fwrite(STDERR, "Error: No run method\n");
            exit(ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
        }

        $block = $runMethod->getElementsByTagName('block')->item(0);

        if ($block === null) {
            fwrite(STDERR, "Error: No block in run method\n");
            exit(ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR);
        }

        $domExecutor = new DOMExecutor($builtinClasses);

        $variables = [];
        $attributes = [];
        $domExecutor->interpretBlockDOM($block, $variables, $attributes);
        exit(ReturnCode::OK);
    }
}
