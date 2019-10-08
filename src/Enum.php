<?php

namespace Vanio\Stdlib;

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
     * The plain value.
     *
     * @var mixed
     */
    protected $plainValue;

    /**
     * The value name.
     *
     * @var string
     */
    private $name;

    /**
     * Prevents instantiating from outside.
     *
     * param mixed $plainValue The plain value.
     */
    protected function __construct($plainValue)
    {
        $this->plainValue = $plainValue;
    }

    /**
     * Box the given plain value.
     * This is just an alias of the method box() with a bit more standard name.
     *
     * @param mixed $plainValue The plain value to be boxed.
     *
     * @return static The boxed value.
     *
     * @throws InvalidArgumentException If the given plain value is not within the enumeration.
     */
    final public static function create($plainValue): self
    {
        return self::box($plainValue);
    }

    /**
     * Box the given plain value.
     *
     * @param mixed $plainValue The plain value to be boxed.
     *
     * @return static The boxed value.
     *
     * @throws InvalidArgumentException If the given plain value is not within the enumeration.
     */
    final public static function box($plainValue): self
    {
        foreach (static::valueNames() as $name) {
            if (self::constant($name) === $plainValue) {
                return self::__callStatic($name, []);
            }
        }

        $message = sprintf('Value %s is not within %s enumeration.', $plainValue, static::class);
        throw new InvalidArgumentException($message);
    }

    /**
     * Get the value with the given name.
     *
     * @param string $valueName The name of the value.
     * @param mixed[] $args The list of arguments. It should be always empty.
     *
     * @return static The requested value.
     */
    final public static function __callStatic(string $valueName, array $args): self
    {
        static $instances = [];

        return $instances[static::class][$valueName]
            ?? $instances[static::class][$valueName] = self::instantiate($valueName);
    }

    /**
     * Get the value with the given name.
     *
     * @param string $name The name of the value.
     *
     * @return static The requested value.
     */
    final public static function valueOf(string $name): self
    {
        return self::__callStatic($name, []);
    }

    /**
     * Get all available values.
     *
     * @return static[]|Iterator All available values.
     */
    final public static function values(): Iterator
    {
        foreach (static::valueNames() as $name) {
            yield self::__callStatic($name, []);
        }
    }

    /**
     * Get all available plain values.
     *
     * @return Iterator All available plain values.
     */
    final public static function plainValues(): Iterator
    {
        foreach (static::valueNames() as $name) {
            yield self::constant($name);
        }
    }

    /**
     * Get names of all the values in this enumeration.
     *
     * @return string[] Names of all the values in this enumeration.
     */
    public static function valueNames(): array
    {
        static $valueNames = [];

        if (!isset($valueNames[static::class])) {
            $valueNames[static::class] = [];

            foreach ((new ReflectionClass(static::class))->getReflectionConstants() as $reflectionConstant) {
                if ($reflectionConstant->isPublic() && !$reflectionConstant->getDeclaringClass()->isInterface()) {
                    $valueNames[static::class][] = $reflectionConstant->name;
                }
            }
        }

        return $valueNames[static::class];
    }

    /**
     * Instantiate value with the given name.
     *
     * @param string $valueName The name of the value.
     *
     * @return static The newly instantiated value.
     */
    private static function instantiate(string $valueName): self
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
     * @return mixed The value of the given constant.
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
     * @throws ErrorException Prevents cloning.
     */
    final public function __clone()
    {
        throw new ErrorException('There can be only one instance for each enumeration value.');
    }

    /**
     * Get properties needed for serialization.
     *
     * @return string[]
     */
    final public function __sleep(): array
    {
        return ['name'];
    }

    /**
     * Get the value with the given name stored in a state array under name key.
     *
     * @param string[] $state
     *
     * @return Enum
     *
     * @throws InvalidArgumentException If the name key is missing
     */
    final public static function __set_state(array $state): self
    {
        if (!array_key_exists('name', $state)) {
            throw new InvalidArgumentException('Cannot get an enumeration value from state array missing "name" key.');
        }

        return self::__callStatic($state['name'], []);
    }

    /**
     * @throws ErrorException Prevents deserialization using unserialize function.
     */
    final public function __wakeup()
    {
        throw new ErrorException('Enumeration values cannot be deserialized.');
    }

    /**
     * Prevents implementing of the \Serializable interface.
     *
     * @codeCoverageIgnore
     * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedMethod
     */
    private function unserialize()
    {}

    /**
     * Get the value name.
     *
     * @return string The value name.
     */
    final public function name(): string
    {
        return $this->name;
    }

    /**
     * Unbox the value.
     *
     * @return mixed The un-boxed value.
     */
    final public function unbox()
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
