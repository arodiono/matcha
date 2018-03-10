<?php

require __DIR__ . '/app/config.php';

return [
    "paths" => [
        "migrations" => "database/migrations",
        "seeds" => "database/seeds"
    ],
    "environments" => [
        "default_migration_table" => "phinxlog",
        "default_database" => "dev",
        "dev" => [
            "adapter" => DB_DRIVER,
            "host" => DB_HOST,
            "name" => DB_NAME,
            "user" => DB_USER,
            "pass" => DB_PASS,
        ]
    ]
];