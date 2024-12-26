<?php

/*
 * This file is part of DbUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class DatabaseTestUtility
{
    protected static $connection;

    protected static $mySQLConnection;

    protected static $postgreSQLConnection;

    public static function getSQLiteMemoryDB()
    {
        if (self::$connection === null) {
            self::$connection = new PDO('sqlite::memory:');
            self::setUpDatabase(self::$connection);
        }

        return self::$connection;
    }

    /**
     * Creates connection to test MySQL database
     *
     * MySQL server must be installed locally, with root access
     * and empty password and listening on unix socket
     *
     * @return PDO
     *
     * @see    DatabaseTestUtility::setUpMySqlDatabase()
     */
    public static function getMySQLDB()
    {
        if (self::$mySQLConnection === null) {
            self::$mySQLConnection = new PDO(
                self::buildMysqlDSN(),
                getenv('MYSQL_DB_USER'),
                getenv('MYSQL_DB_PASSWORD')
            );

            self::setUpMySQLDatabase(self::$mySQLConnection);
        }

        return self::$mySQLConnection;
    }

    /**
     * Creates connection to test PostgreSQL database
     *
     * PostgreSQL server must be installed locally, with appropriate access
     * credentials set in environment variables
     *
     * @return PDO
     */
    public static function getPostgreSQLDB()
    {
        if (self::$postgreSQLConnection === null) {
            self::$postgreSQLConnection = new PDO(
                self::buildPostgreSQLDSN(),
                getenv('POSTGRES_DB_USER'),
                getenv('POSTGRES_DB_PASSWORD')
            );

            self::setUpPostgreSQLDatabase(self::$postgreSQLConnection);
        }

        return self::$postgreSQLConnection;
    }

    protected static function setUpDatabase(PDO $connection): void
    {
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS table1 (
            table1_id INTEGER PRIMARY KEY AUTOINCREMENT,
            column1 VARCHAR(20),
            column2 INT(10),
            column3 DECIMAL(6,2),
            column4 TEXT
          )'
        );

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS table2 (
            table2_id INTEGER PRIMARY KEY AUTOINCREMENT,
            column5 VARCHAR(20),
            column6 INT(10),
            column7 DECIMAL(6,2),
            column8 TEXT
          )'
        );

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS table3 (
            table3_id INTEGER PRIMARY KEY AUTOINCREMENT,
            column9 VARCHAR(20),
            column10 INT(10),
            column11 DECIMAL(6,2),
            column12 TEXT
          )'
        );
    }

    /**
     * Creates default testing schema for MySQL database
     *
     * Tables must contain foreign keys and use InnoDb storage engine
     * for constraint tests to be executed properly
     *
     * @param PDO $connection PDO instance representing connection to MySQL database
     *
     * @see   DatabaseTestUtility::getMySQLDB()
     */
    protected static function setUpMySqlDatabase(PDO $connection): void
    {
        $connection->exec(
            'CREATE TABLE IF NOT EXISTS table1 (
            table1_id INTEGER AUTO_INCREMENT,
            column1 VARCHAR(20),
            column2 INT(10),
            column3 DECIMAL(6,2),
            column4 TEXT,
            PRIMARY KEY (table1_id)
          ) ENGINE=INNODB;
        '
        );

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS table2 (
            table2_id INTEGER AUTO_INCREMENT,
            table1_id INTEGER,
            column5 VARCHAR(20),
            column6 INT(10),
            column7 DECIMAL(6,2),
            column8 TEXT,
            PRIMARY KEY (table2_id),
            FOREIGN KEY (table1_id) REFERENCES table1(table1_id)
          ) ENGINE=INNODB;
        '
        );

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS table3 (
            table3_id INTEGER AUTO_INCREMENT,
            table2_id INTEGER,
            column9 VARCHAR(20),
            column10 INT(10),
            column11 DECIMAL(6,2),
            column12 TEXT,
            PRIMARY KEY (table3_id),
            FOREIGN KEY (table2_id) REFERENCES table2(table2_id)
          ) ENGINE=INNODB;
        '
        );
    }

    protected static function setUpPostgreSQLDatabase(PDO $connection): void
    {
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $connection->exec('DROP TABLE if exists table1');
        $connection->exec(
            'CREATE TABLE table1 (
            table1_id SERIAL PRIMARY KEY,
            column1 VARCHAR(20),
            column2 INT,
            column3 DECIMAL(6,2),
            column4 TEXT
          )
          '
        );

        $connection->exec('DROP TABLE if exists table2');
        $connection->exec(
            'CREATE TABLE table2 (
            table2_id SERIAL PRIMARY KEY,
            table1_id INTEGER,
            column5 VARCHAR(20),
            column6 INT,
            column7 DECIMAL(6,2),
            column8 TEXT
          )'
        );

        $connection->exec('DROP TABLE if exists table3');
        $connection->exec(
            'CREATE TABLE table3 (
            table3_id SERIAL PRIMARY KEY,
            table2_id INTEGER,
            column9 VARCHAR(20),
            column10 INT,
            column11 DECIMAL(6,2),
            column12 TEXT
          )'
        );
    }

    private static function buildMysqlDSN(): string
    {
        return sprintf(
            'mysql:host=%s;dbname=%s;port=%s',
            getenv('MYSQL_DB_HOST'),
            getenv('MYSQL_DB_NAME'),
            getenv('MYSQL_DB_PORT')
        );
    }

    private static function buildPostgreSQLDSN(): string
    {
        return sprintf(
            'pgsql:host=%s;dbname=%s;port=%s',
            getenv('POSTGRES_DB_HOST'),
            getenv('POSTGRES_DB_NAME'),
            getenv('POSTGRES_DB_PORT')
        );
    }
}
