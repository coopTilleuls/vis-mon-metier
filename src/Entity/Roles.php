<?php

declare(strict_types=1);

namespace App\Entity;

abstract class Roles
{
    public const ADMIN = 'ROLE_ADMIN';
    public const REFERENT = 'ROLE_REFERENT';
    public const LECTEUR = 'ROLE_LECTEUR';
}
