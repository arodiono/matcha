<?php

require __DIR__. '/app/Database/config.php';

return [
    "paths" => [
        "migrations" => "app/Database/migrations",
        "seeds" => "app/Database/seeds"
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
            "port" => DB_PORT,
            "charset" => DB_CHARSET,
            "collation" => DB_COLLATION
        ]
    ]
];