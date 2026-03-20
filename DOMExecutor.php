<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file DOMExecutor.php
 * @brief The main class where run method processing and interpretation of the code takes place
 * @date 22.04.2025
 */

namespace IPP\Student;

use DOMElement;
use IPP\Core\ReturnCode;

class DOMExecutor
{
    private BuiltInClasses $classes;

    public function __construct(BuiltInClasses $classes)
    {
        $this->classes = $classes;
    }

    /**
     * @brief The function processes the block
     * @param array<string, mixed> $variables
     * @param array<string, mixed> $attributes
     */
    public function interpretBlockDOM(DOMElement $block, array &$variables, array &$attributes): mixed
    {
        $assigns = [];

        // Collect all <assign> elements and index them by their execution order
        foreach ($block->childNodes as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'assign') {
                $order = (int)$child->getAttribute('order');
                $assigns[$order] = $child;
            }
        }

        ksort($assigns);
        $lastValue = null;
        foreach ($assigns as $assign) {
            $varNode = $assign->getElementsByTagName('var')->item(0);
            $exprNode = $assign->getElementsByTagName('expr')->item(0);

            if (!$varNode || !$exprNode) {
                fwrite(STDERR, "Error: Missing 'var' or 'expr' node\n");
                exit(ReturnCode::INTERPRET_TYPE_ERROR);
            }

            $varName = $varNode->getAttribute('name');
            $value = $this->evaluateExpressionDOM($exprNode, $variables, $attributes);
            // Wrap the result into an object and assign it
            $variables[$varName] = $this->wrapValueIntoObject($value, $attributes, $this->classes);
            $lastValue = $variables[$varName];
        }
        return $lastValue;
    }

    /**
     * @brief Evaluates an <expr> node and dispatches to the appropriate evaluator
     * @param array<string, mixed> $variables
     * @param array<string, mixed> $attributes
     */
    public function evaluateExpressionDOM(DOMElement $expr, array &$variables, array &$attributes): mixed
    {
        foreach ($expr->childNodes as $node) {
            if (!($node instanceof DOMElement)) {
                continue;
            }
            return match ($node->tagName) {
                'literal' => $this->evalLiteral($node),
                'var'     => $this->evalVariable($node, $variables),
                'send'    => $this->evalSend($node, $variables, $attributes),
                'block'   => $this->evalBlock($node, $attributes),
                default   => null,
            };
        }

        return null;
    }


    private function evalLiteral(DOMElement $node): mixed
    {
        $class = $node->getAttribute('class');
        $value = $node->getAttribute('value');

        return match ($class) {
            'Integer' => new IntegerClass((int)$value),
            'String' => new StringClass(str_replace('\n', PHP_EOL, htmlspecialchars_decode($value))),
            'True' => new TrueClass(),
            'False' => new FalseClass(),
            'Nil' => new NilClass(),
            'class' => new ("\\IPP\\Student\\" . $value . "Class")(),
            default => $value,
        };
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function evalVariable(DOMElement $node, array $variables): mixed
    {
        $name = $node->getAttribute('name');
        if ($name === 'self') {
            return 'self';
        }
        if ($variables[$name] == null) {
            fwrite(STDERR, "Error: " . $name . " variable is not defined\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        } else {
            return $variables[$name];
        }
    }

    /**
     * @param array<string, mixed> $variables
     * @param array<string, mixed> $attributes
     */
    private function evalSend(DOMElement $node, array &$variables, array &$attributes): mixed
    {
        $selector = $node->getAttribute('selector');

        $receiverExpr = null;
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'expr') {
                $receiverExpr = $child;
                break;
            }
        }

        if ($receiverExpr === null) {
            fwrite(STDERR, "Error: Missing receiver expression\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }

        $receiver = $this->evaluateExpressionDOM($receiverExpr, $variables, $attributes);

        $args = [];
        // Extract method arguments
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'arg') {
                $order = (int)$child->getAttribute('order');
                foreach ($child->childNodes as $expr) {
                    if ($expr instanceof DOMElement && $expr->tagName === 'expr') {
                        $args[$order - 1] = $this->evaluateExpressionDOM($expr, $variables, $attributes);
                        break;
                    }
                }
            }
        }


        ksort($args);
        $args = array_values($args);

        // Check if self
        if ($receiver === 'self') {
            $method = $this->classes->findMethodDOM($this->classes->getCurrentClass(), $selector);
            if ($method !== null) {
                $block = $method->getElementsByTagName('block')->item(0);
                if ($block === null) {
                    fwrite(STDERR, "Error: Block node is missing\n");
                    exit(ReturnCode::INTERPRET_TYPE_ERROR);
                }
                $blockObject = new BlockClass($block, $attributes, $this->classes);

                $result = $blockObject->value($args);

                foreach ($blockObject->variables as $varName => $value) {
                    $attributes[$varName] = $this->wrapValueIntoObject($value, $attributes, $this->classes);
                }

                return $result;
            } elseif (str_ends_with($selector, ':')) {
                // Assignment to attribute
                $name = rtrim($selector, ':');
                $value = $args[0] ?? null;
                $attributes[$name] = $this->wrapValueIntoObject($value, $attributes, $this->classes);
                return $attributes[$name];
            } else {
                // Accessing to attribute
                if (!array_key_exists($selector, $attributes)) {
                    fwrite(STDERR, "Error: Attribute '$selector' is not defined\n");
                    exit(ReturnCode::INTERPRET_DNU_ERROR);
                }
                return $attributes[$selector];
            }
        }

        // Send to a block
        if ($receiver instanceof BlockClass) {
            try {
                if ($selector === 'value') {
                    return $receiver->$selector($args);
                } elseif ($selector === 'whileTrue:') {
                    return $receiver->whileTrue($args);
                } else {
                    return $receiver->call($selector, $args);
                }
            } catch (\Throwable) {
                fwrite(STDERR, "Error: BlockClass does not understand selector '$selector'\n");
                exit(ReturnCode::INTERPRET_DNU_ERROR);
            }
        } else {
            // Regular method call of built-in methods
            $selector = str_replace(':', '', $selector);
            try {
                return $receiver->$selector($args);
            } catch (\Throwable) {
                $errorMessage = "Error: Object of class " .
                    (is_object($receiver) ? get_class($receiver) : gettype($receiver)) .
                    " does not understand selector '$selector'\n";
                fwrite(STDERR, $errorMessage);
                exit(ReturnCode::INTERPRET_DNU_ERROR);
            }
        }
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function evalBlock(DOMElement $node, array &$attributes): BlockClass
    {
        return new BlockClass(
            $node,
            $attributes,
            $this->classes
        );
    }

    /**
     * @brief Gets the class name for a given value
     */
    private function getTypeForValue(mixed $value): string
    {
        if ((is_object($value) && $value instanceof IntegerClass)) {
            return IntegerClass::class;
        } elseif ((is_object($value) && $value instanceof StringClass)) {
            return StringClass::class;
        } elseif ((is_object($value) && $value instanceof TrueClass)) {
            return TrueClass::class;
        } elseif ((is_object($value) && $value instanceof FalseClass)) {
            return FalseClass::class;
        } elseif ((is_object($value) && $value instanceof NilClass)) {
            return NilClass::class;
        } elseif (is_object($value) && $value instanceof BlockClass) {
            return BlockClass::class;
        } else {
            return ObjectClass::class;
        }
    }

    /**
     * @brief Wraps a raw value into an appropriate class wrapper
     * @param mixed $value
     * @param array<string, mixed> $attributes
     */
    private function wrapValueIntoObject($value, array $attributes, BuiltInClasses $classes): mixed
    {
        if ($value instanceof ObjectClass) {
            return $value;
        }
        $className = $this->getTypeForValue($value);

        if (!class_exists($className)) {
            fwrite(STDERR, "Error: Class is not found\n");
            exit(ReturnCode::INTERPRET_DNU_ERROR);
        }

        if (is_object($value) && property_exists($value, 'value')) {
            return new $className($value->value);
        }

        return new $className($value);
    }
}
