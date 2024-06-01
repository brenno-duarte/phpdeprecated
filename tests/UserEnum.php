<?php

namespace DeprecatedTest;

use Deprecated\Deprecated;

enum UserEnum {
    #[Deprecated()]
    case NAME;
}