<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2017-06
 */

namespace Runner\NezhaCashier\Utils;

use ArrayAccess;
use Countable;

class Collection implements ArrayAccess, Countable
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Collection constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * @param $key
     * @param null $default
     *
     * @return array|mixed|null
     */
    public function get($key, $default = null)
    {
        if (false === strpos($key, '.')) {
            return $this->data[$key] ?? $default;
        }

        $keys = explode('.', $key);

        $target = &$this->data;

        foreach ($keys as $key) {
            if (!array_key_exists($key, $target)) {
                return $default;
            }
            $target = &$target[$key];
        }

        return $target;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        if (false === strpos($key, '.')) {
            return $this->offsetSet($key, $value);
        }

        $keys = explode('.', $key);

        $target = &$this->data;

        foreach ($keys as $key) {
            if (isset($target[$key]) && !is_array($target[$key])) {
                $target[$key] = (array) $target[$key];
            }
            $target = &$target[$key];
        }

        $target = $value;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        if (false === strpos($key, '.')) {
            return $this->offsetExists($key);
        }

        $keys = explode('.', $key);

        $target = &$this->data;

        foreach ($keys as $key) {
            if (!array_key_exists($key, $target)) {
                return false;
            }
            $target = &$target[$key];
        }

        return true;
    }
}
