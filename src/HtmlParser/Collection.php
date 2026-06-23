<?php

declare(strict_types=1);

namespace Wilsenhc\ParseContribuyente\HtmlParser;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Wilsenhc\ParseContribuyente\HtmlParser\Exceptions\EmptyCollectionException;

use function call_user_func_array;
use function count;
use function is_callable;
use function reset;
use ReturnTypeWillChange;

/**
 * A ordered list of Nodes returned by `find()`.
 *
 * Mirrors the original Collection: countable, traversable,
 * array-accessible, and able to delegate method/property access to its
 * first node via __call/__get.
 *
 * @method string      innerText()
 * @method string      text()
 * @method Node|null   firstChild()
 * @implements IteratorAggregate<int, Node>
 * @implements ArrayAccess<int, Node>
 */
final class Collection implements IteratorAggregate, ArrayAccess, Countable
{
    /** @var array<int, Node> */
    private array $nodes;

    /**
     * @param array<int, Node> $nodes
     */
    public function __construct(array $nodes = [])
    {
        $this->nodes = array_values($nodes);
    }

    /**
     * Delegates a method call to the first node in the collection.
     *
     * @param array<int, mixed> $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        $node = reset($this->nodes);
        if ($node instanceof Node && is_callable([$node, $method])) {
            return call_user_func_array([$node, $method], $arguments);
        }

        throw new EmptyCollectionException(
            'The collection does not contain any Nodes that can handle "' . $method . '".',
        );
    }

    /**
     * Delegates property access to the first node in the collection.
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        $node = reset($this->nodes);
        if ($node instanceof Node) {
            return $node->$key;
        }

        throw new EmptyCollectionException('The collection does not contain any Nodes.');
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * @return ArrayIterator<int, Node>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->nodes);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->nodes[$offset]);
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->nodes[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->nodes[] = $value;
        } else {
            $this->nodes[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->nodes[$offset]);
    }

    /**
     * @return array<int, Node>
     */
    public function toArray(): array
    {
        return $this->nodes;
    }
}