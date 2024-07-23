<?php

namespace Deprecated;

use Deprecated\Output\ConsoleOutput;

#[\Attribute(\Attribute::TARGET_ALL)]
final class Deprecated
{
    /**
     * @var null|string
     */
    public readonly ?string $message;

    /**
     * @var null|string
     */
    public readonly ?string $since;

    /**
     * Mark a resource/function as deprecated
     *
     * @param string|null $message Error message
     * @param string|null $since   Indicate since when the element having the attribute is deprecated
     */
    public function __construct(?string $message = null, ?string $since = null)
    {
        $this->message = $message;
        $this->since = $since;
    }

    /**
     * Show message
     *
     * @param string $type
     * @param string $name
     * 
     * @return void
     */
    public function addDeprecatedMessage(string $type, string $name, ?string $class_name = null): void
    {
        (!is_null($class_name)) ? $class_name = " in " . $class_name : "";
        $name = ConsoleOutput::warning($name)->getMessage();
        $message = ucfirst($type) . " " . $name . " is deprecated" . $class_name;

        if ($this->since != null) $message = $message . " since " . $this->since;
        if ($this->message != null) $message = $message . ", " . $this->message;

        (is_null($class_name)) ?
            ConsoleOutput::error("Deprecated:")->print() :
            ConsoleOutput::info("Deprecated in class:")->print();

        ConsoleOutput::line(" " . $message)->print()->break();
    }
}
