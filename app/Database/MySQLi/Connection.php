<?php

namespace App\Database\MySQLi;

use CodeIgniter\Database\MySQLi\Connection as BaseConnection;

/**
 * MySQLi connection with a development-compatible SQL mode.
 *
 * The app was built and tested against MariaDB, whose default SQL mode does
 * not enable ANSI_QUOTES or ONLY_FULL_GROUP_BY. Managed MySQL providers such
 * as Aiven enable both by default, which makes legacy report queries that use
 * double-quoted string literals or group by a ticket while selecting its
 * offense rows fail. Apply the same relaxed mode to each session so hosted
 * deployments behave like the local database without rewriting every query.
 */
class Connection extends BaseConnection
{
    public function connect(bool $persistent = false)
    {
        $connection = parent::connect($persistent);

        // BaseConnection assigns the return value to $this->connID only after
        // this method returns, so use the value we just got back.
        if ($connection instanceof \mysqli) {
            try {
                $connection->query(
                    "SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'"
                );
            } catch (\mysqli_sql_exception) {
                // Never stop the app from connecting if the provider forbids
                // changing the session mode.
            }
        }

        return $connection;
    }
}
