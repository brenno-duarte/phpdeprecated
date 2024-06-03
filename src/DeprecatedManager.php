<?php

namespace Deprecated;

use Deprecated\Output\{ColorsEnum, ConsoleOutput};

final class DeprecatedManager
{
    /**
     * @var string
     */
    private static string $class_name = '';

    /**
     * @var array
     */
    private static array $attributes = [];

    /**
     * @var bool|null|null
     */
    private static bool $deprecated_exists = false;

    /**
     * @var string
     */
    private const ATTRIBUTE_NAME = "Deprecated\Deprecated";

    /**
     * Validate all files
     *
     * @param array $map
     * @param array $functions
     * 
     * @return void
     */
    public static function checkForDeprecated(array $map, array $functions): void
    {
        $deprecated_class_exists = false;
        $deprecated_function_exists = false;

        /** Read resources */
        ConsoleOutput::banner("Deprecated resources (class, interface, traits, ...)", ColorsEnum::BG_LIGHT_BLUE)
            ->print()->break();

        foreach ($map as $namespace => $file) {
            $deprecated_class_exists = DeprecatedManager::checkIfObjectIsDeprecated($namespace);
        }

        if ($deprecated_class_exists == false) {
            ConsoleOutput::success("No deprecated resources found!")->print()->break();
        }

        /** Read functions */
        if (!empty($functions)) {
            $deprecated_function_exists = DeprecatedManager::checkIfFunctionIsDeprecated($functions);

            if ($deprecated_function_exists == false) {
                ConsoleOutput::banner("Deprecated functions", ColorsEnum::BG_LIGHT_BLUE)->print()->break();
                ConsoleOutput::success("No deprecated functions found!")->print()->break();
            }
        }
    }

    /**
     * Read class
     *
     * @param object|string $class
     * 
     * @return bool
     */
    private static function checkIfObjectIsDeprecated(object|string $class): bool
    {
        try {
            $reflection = new \ReflectionClass($class);
            self::$class_name = $reflection->getName();
            self::$attributes = $reflection->getAttributes();

            self::checkIfObjectHasDeprecatedResource($class);

            /** If object is a class */
            if (!$reflection->isTrait() && !$reflection->isInterface() && !$reflection->isEnum()) {
                self::getDeprecatedAttributes("class", self::$class_name, self::$attributes);
            }

            /** If object ISN'T a class */
            if ($reflection->isTrait()) {
                self::getDeprecatedAttributes("trait", self::$class_name, self::$attributes);
            }

            if ($reflection->isInterface()) {
                self::getDeprecatedAttributes("interface", self::$class_name, self::$attributes);
            }

            if ($reflection->isEnum()) {
                $reflection_enum = new \ReflectionEnum($class);

                foreach ($reflection_enum->getCases() as $case) {
                    self::getDeprecatedAttributes(
                        "enum case",
                        $reflection_enum->getName() . "::" . $case->getName(),
                        $case->getAttributes(static::ATTRIBUTE_NAME)
                    );
                }
            }

            /** Get all methods from object */
            foreach ($reflection->getMethods() as $method) {
                self::getDeprecatedAttributes(
                    "method",
                    self::$class_name . "::" . $method->getName() . "()",
                    $method->getAttributes(static::ATTRIBUTE_NAME)
                );
            }

            /** Get all properties from object */
            foreach ($reflection->getProperties() as $property) {
                self::getDeprecatedAttributes(
                    "property",
                    "$" . $property->getName(),
                    $property->getAttributes(static::ATTRIBUTE_NAME)
                );
            }

            /** Get all static properties from object */
            foreach ($reflection->getProperties(\ReflectionProperty::IS_STATIC) as $staticProperties) {
                self::getDeprecatedAttributes(
                    "static property",
                    "$" . $staticProperties->getName(),
                    $staticProperties->getAttributes()
                );
            }

            /** Get all enums from object */
            if (!$reflection->isEnum()) {
                foreach ($reflection->getConstants() as $constant => $value) {
                    $reflection_constants = new \ReflectionClassConstant($class, $constant);
                    self::getDeprecatedAttributes(
                        "constant",
                        self::$class_name . "::" . $reflection_constants->getName(),
                        $reflection_constants->getAttributes(static::ATTRIBUTE_NAME)
                    );
                }
            }

            return self::$deprecated_exists;
        } catch (\ReflectionException) {
            return false;
        }
    }

    /**
     * Check if object has deprecated resources
     *
     * @param object|string $class
     * 
     * @return bool
     */
    private static function checkIfObjectHasDeprecatedResource(object|string $class): bool
    {
        $reflection = new \ReflectionClass($class);
        self::$class_name = $reflection->getName();
        self::$attributes = $reflection->getAttributes();

        foreach ($reflection->getTraits() as $trait) {
            if (!empty($trait)) {
                self::getDeprecatedAttributes(
                    "trait",
                    self::getClassWithoutNamespace($trait->getName()),
                    $trait->getAttributes(static::ATTRIBUTE_NAME),
                    self::$class_name
                );
            }
        }

        foreach ($reflection->getInterfaces() as $interface) {
            if (!empty($interface)) {
                self::getDeprecatedAttributes(
                    "interface",
                    self::getClassWithoutNamespace($interface->getName()),
                    $interface->getAttributes(static::ATTRIBUTE_NAME),
                    self::$class_name
                );
            }
        }

        if ($reflection->getParentClass() != false) {
            $parent_name = $reflection->getParentClass();
            $parent_reflection = new \ReflectionClass($parent_name->name);
            self::getDeprecatedAttributes(
                "parent class",
                $parent_reflection->getName(),
                $parent_reflection->getAttributes(),
                self::$class_name
            );
        }

        return self::$deprecated_exists;
    }

    /**
     * Read functions
     *
     * @param array $functions
     * 
     * @return bool
     */
    private static function checkIfFunctionIsDeprecated(array $functions): bool
    {
        foreach ($functions as $function) {
            $reflection = new \ReflectionFunction($function);
            self::getDeprecatedAttributes("function", $reflection->getName() . "()", $reflection->getAttributes());
        }

        return self::$deprecated_exists;
    }

    /**
     * Read attributes
     *
     * @param string $type
     * @param string $name
     * @param array $attributes
     * 
     * @return void
     */
    private static function getDeprecatedAttributes(
        string $type,
        string $name,
        array $attributes,
        ?string $class_name = null
    ): void {
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                $deprecated_attr = $attribute->getName();

                if ($deprecated_attr === self::ATTRIBUTE_NAME) {
                    self::$deprecated_exists = true;
                    $instance = $attribute->newInstance();
                    $instance->addDeprecatedMessage($type, $name, $class_name);
                }
            }
        }
    }

    private static function getClassWithoutNamespace(string|object $classname): string
    {
        if (is_object($classname)) {
            $classname = get_class($classname);
        }

        $class = explode("\\", $classname);
        return end($class);
    }

    public static function classLoaderInDirectory(string $directory): void
    {
        $dir      = new \RecursiveDirectoryIterator($directory);
        $iterator = new \RecursiveIteratorIterator($dir);

        foreach ($iterator as $file) {
            $fname = $file->getFilename();

            if (preg_match('%\.php$%', $fname)) {
                if (!str_contains($file->getPathname(), DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR)) {
                    require_once $file->getPathname();
                    //var_dump($file->getPathname());
                }
            }
        }
    }
}
