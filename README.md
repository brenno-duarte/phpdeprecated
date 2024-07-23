# PHP Deprecated component

PHP component used to search for deprecated resources in your project, such as classes, traits, enums, and others. This component also searches for subclasses that are deprecated.

## Requeirements

- PHP >= 8.3

## Installation

```bash
composer require brenno-duarte/phpdeprecated
```

## How to use

To use this component, let's assume you have a class called `User`. However, you want to discontinue this class. You must add the `Deprecated` attribute to this class:

```php
<?php

use Deprecated\Deprecated;

#[Deprecated()]
class User
{
}
```

This way, this class will be marked as deprecated. You can add a message in the attribute constructor and also the date the class was deprecated.

```php
<?php

use Deprecated\Deprecated;

#[Deprecated("use other class", "2024-06-01")]
class User
{
}
```

You can add this attribute to classes, traits, properties, interfaces and methods.

Example:

```php
<?php

use Deprecated\Deprecated;

#[Deprecated()]
class User
{
    #[Deprecated(since: '2024')]
    const USER = '';

    #[Deprecated(since: '2024')]
    private string $name;
    
    #[Deprecated('Use another method instead', 2024)]
    public function method1()
    {
    }
}
```

## Checking deprecated resources

To check if exists deprecated resources with `Deprecated` attribute, simply run the command below in the terminal:

```bash
vendor/bin/phpdeprecated <directory>
```

Replace the `<directory>` with the name of the directory you want to search for deprecated resources. The end result will be similar to the image below:

![php deprecated component](image.png)

## Using `@deprecated`

This component also supports annotations containing `@deprecated`. However, it doesn't support messages like the `Deprecated` attribute.

This component will first search for the `Deprecated` attribute and, if it doesn't find it, it will search for the `@deprecated` annotation.

Example:

```php
<?php

use Deprecated\Deprecated;

/**
 * @deprecated
 */
#[Deprecated()]
class User
{
    /**
     * @deprecated
     */
    #[Deprecated(since: '2024')]
    const USER = '';

    /**
     * @deprecated
     */
    #[Deprecated(since: '2024')]
    private string $name;
    
    /**
     * @deprecated
     */
    #[Deprecated('Use another method instead', 2024)]
    public function method1()
    {
    }
}
```

You can use both (`Deprecated` attribute and `@deprecated` annotation) at the same time. However, for reasons of code readability, it's recommended to use the attribute instead of the annotation.