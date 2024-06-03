<?php

namespace DeprecatedTest\Lib;

use Deprecated\Deprecated;

enum UserEnum {
    #[Deprecated()]
    case NAME;
}