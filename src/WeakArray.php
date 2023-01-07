<?php

namespace WeakArray;

use WeakMap;
use WeakReference;

/**
 * Weak Array
 *
 * Array value is weak reference.
 */
class WeakArray implements \ArrayAccess, \Countable {

    /** @var WeakReference[] */
    private array $array = [];

    /** @var WeakMap<object, DestructDetector[]> */
    private WeakMap $destructDetectors;

    public function __construct(array $objects=[])
    {
        $this->destructDetectors = new WeakMap();

        foreach ($objects as $i => $obj) {
            $this->offsetSet($i, $obj);
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->array[$offset]) && $this->array[$offset]->get() !== null;
    }

    public function offsetGet(mixed $offset)
    {
        return isset($this->array[$offset]) ? $this->array[$offset]->get() : null;
    }

    public function offsetSet($offset, $value): void
    {
        $ref = WeakReference::create($value);

        if ($offset === null) {
            $this->array[] = $ref;
            $offset = array_key_last($this->array);
        } else {
            $this->array[$offset] = $ref;
        }

        $destructDetector = new DestructDetector($this, $offset);

        if (isset($this->destructDetectors[$value])) {
            $this->destructDetectors[$value][$offset] = $destructDetector;
        } else {
            $this->destructDetectors[$value] = [$offset => $destructDetector];
        }
    }

    public function offsetUnset($offset): void
    {
        if (isset($this->array[$offset])) {
            $value = $this->array[$offset]->get();

            if ($value !== null) {
                $this->destructDetectors[$value][$offset]->deactivate();
                unset($this->destructDetectors[$value][$offset]);
            }

            unset($this->array[$offset]);
        }
    }

    public function count(): int
    {
        return count($this->array);
    }

    public function __destruct()
    {
        //deactivate to prevent destruct detector double free
        foreach ($this->destructDetectors as $dds) {
            foreach ($dds as $dd) {
                $dd->deactivate();
            }
        }
    }

}

/**
 * Destruct Detector
 *
 * Remove index relation in weak array when destruct.
 */
class DestructDetector {

    private WeakReference $weakArray;
    private $active = true;

    public function __construct(WeakArray $weakArray, private $key)
    {
        $this->weakArray = WeakReference::create($weakArray);
    }

    public function __destruct()
    {
        if ($this->active && ($weakArray = $this->weakArray->get()) !== null) {
            unset($weakArray[$this->key]);
        }
    }

    /**
     * Deactivate when manual unset from weak array to resolve double unset
     */
    public function deactivate()
    {
        $this->active = false;
    }

}
