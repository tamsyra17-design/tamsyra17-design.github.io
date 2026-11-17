<?php

namespace App\Support;

/**
 * The staff roles the shop recognises.
 *
 * Kept in one place so the seeder, the panel access check and the user policy
 * cannot drift apart.
 */
final class Roles
{
    public const SUPER_ADMIN = 'super_admin';

    /**
     * Every role that may sign in to the admin panel.
     *
     * @var list<string>
     */
    public const ALL = [
        self::SUPER_ADMIN,
        'catalog_manager',
        'order_manager',
        'rental_manager',
        'content_manager',
        'support',
    ];

    private function __construct()
    {
        // Constants only.
    }
}
