<?php

namespace Vanio\Language;

use ErrorException;
use InvalidArgumentException;
use Iterator;
use ReflectionClass;

/**
 * The common base class for all enumeration types.
 */
abstract class Enum
{
    /**
     * The value name.
     *
     * @var string
     */
    private $name;

    /**
     * Get the value with the given name.
     *
     * @param string $valueName The name of the value.
     * @param mixed[] $args The list of arguments. It should be always empty.
     *
     * @return static The requested value.
     */
    public final static function __callStatic(string $valueName, array $args): self
    {
        static $instances = [];

        return !isset($instances[static::class][$valueName])
            ? $instances[static::class][$valueName] = self::create($valueName)
            : $instances[static::class][$valueName];
    }

    /**
     * Get the value with the given name.
     *
     * @param string $name The name of the value.
     *
     * @return static The requested value.
     */
    public final static function valueOf(string $name): self
    {
        return self::__callStatic($name, []);
    }

    /**
     * Get all available values.
     *
     * @return static[]|Iterator All available values.
     */
    public final static function values(): Iterator
    {
        foreach (self::constants() as $name) {
            yield self::__callStatic($name, []);
        }
    }

    /**
     * Box the given plain value.
     *
     * @param scalar|array|null $plainValue The plain value to be boxed.
     *
     * @return static The boxed value.
     *
     * @throws InvalidArgumentException If the given plain value is not within the enumeration.
     */
    public final static function box($plainValue): self
    {
        foreach (self::constants() as $name) {
            if (self::constant($name) === $plainValue) {
                return self::__callStatic($name, []);
            }
        }

        $message = sprintf('Value %s is not within %s enumeration.', $plainValue, static::class);
        throw new InvalidArgumentException($message);
    }

    /**
     * Create value with the given name.
     *
     * @param string $valueName The name of the value.
     *
     * @return static The newly created value.
     */
    private static function create(string $valueName): self
    {
        $value = new static(...(array) self::constant($valueName));
        $value->name = $valueName;

        return $value;
    }

    /**
     * Get the value of the given constant.
     *
     * @param string $name The name of the constant.
     *
     * @return scalar|array|null The value of the given constant.
     *
     * @throws InvalidArgumentException If no such constant exists.
     */
    private static function constant(string $name)
    {
        $constant = static::class . '::' . $name;

        if (!defined($constant)) {
            $message = sprintf('Enum %s does not contain value named %s.', static::class, $name);
            throw new InvalidArgumentException($message);
        }

        return constant($constant);
    }

    /**
     * Get all the class constants.
     *
     * @return string[] All the class constants.
     */
    private static function constants(): array
    {
        static $constants = [];

        return !isset($constants[static::class])
            ? $constants[static::class] = array_keys((new ReflectionClass(static::class))->getConstants())
            : $constants[static::class];
    }

    /**
     * Prevents instantiating from outside.
     */
    protected function __construct()
    {}

    /**
     * @throws ErrorException Prevents cloning.
     */
    public final function __clone()
    {
        throw new ErrorException('Enumeration values are singletons and therefore cannot be cloned.');
    }

    /**
     * @throws ErrorException Prevents serialization.
     */
    public final function __sleep()
    {
        throw new ErrorException('Enumeration values cannot be serialized.');
    }

    /**
     * @throws ErrorException Prevents deserialization.
     */
    public final function __wakeup()
    {
        throw new ErrorException('Enumeration values cannot be deserialized.');
    }

    /**
     * Prevents implementing of the \Serializable interface.
     *
     * @codeCoverageIgnore
     */
    private function unserialize()
    {}

    /**
     * Get the value name.
     *
     * @return string The value name.
     */
    public final function name(): string
    {
        return $this->name;
    }

    /**
     * Unbox the value.
     *
     * @return scalar|array|null The un-boxed value.
     */
    public final function unbox()
    {
        return self::constant($this->name);
    }

    /**
     * Get the string representation of the value.
     *
     * @return string The string representation of the value.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
