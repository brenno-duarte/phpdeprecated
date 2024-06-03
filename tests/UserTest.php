<?php

namespace DeprecatedTest;

use Deprecated\Deprecated;
use DeprecatedTest\Lib\{ParentClass, UserInterface, UserTrait};

#[Deprecated()]
class UserTest extends ParentClass implements UserInterface
{
    use UserTrait;

    /**
     * @deprecated constant deprecated
     */
    #[Deprecated(since: '2024')]
    const USER = '';

    /**
     * @deprecated property deprecated
     * @var string
     */
    #[Deprecated(since: '2024')]
    private string $name;

    /**
     * @deprecated property static deprecated
     */
    #[Deprecated(since: '2024')]
    private static string $age;
    
    /**
     * @deprecated Use another
     * @param string
     * @return void
     */
    public function method1()
    {
        
    }

    /**
     * @deprecated not used
     */
    #[Deprecated('Use "method1" instead')]
    public static function methodStatic()
    {
        
    }
}