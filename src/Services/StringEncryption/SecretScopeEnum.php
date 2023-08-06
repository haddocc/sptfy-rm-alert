<?php

namespace App\Services\StringEncryption;

enum SecretScopeEnum
{
    case Local;
    case Global;
    case All;
}
