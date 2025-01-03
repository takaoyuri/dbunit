<?php

/*
 * This file is part of DbUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\DbUnit\Tests\Unit\DataSet;

use DatabaseTestUtility;
use PHPUnit\DbUnit\Database\DefaultConnection;
use PHPUnit\DbUnit\DataSet\DefaultTable;
use PHPUnit\DbUnit\DataSet\DefaultTableMetadata;
use PHPUnit\DbUnit\DataSet\ITable;
use PHPUnit\DbUnit\DataSet\QueryDataSet;
use PHPUnit\DbUnit\TestCase;

class QueryDataSetTest extends TestCase
{
    /**
     * @var ITable[]
     */
    protected $dataSet;

    protected $pdo;

    protected function setUp(): void
    {
        $this->pdo = DatabaseTestUtility::getSQLiteMemoryDB();

        parent::setUp();

        $this->dataSet = new QueryDataSet($this->getConnection());
        $this->dataSet->addTable('table1');
        $this->dataSet->addTable('query1', '
            SELECT
                t1.column1 tc1, t2.column5 tc2
            FROM
                table1 t1
                JOIN table2 t2 ON t1.table1_id = t2.table2_id
        ');
    }

    public function testGetTable(): void
    {
        $expectedTable1 = $this->getConnection()->createDataSet(['table1'])->getTable('table1');

        $expectedTable2 = new DefaultTable(
            new DefaultTableMetadata('query1', ['tc1', 'tc2'])
        );

        $expectedTable2->addRow([
            'tc1' => 'bar',
            'tc2' => 'blah',
        ]);

        self::assertTablesEqual($expectedTable1, $this->dataSet->getTable('table1'));
        self::assertTablesEqual($expectedTable2, $this->dataSet->getTable('query1'));
    }

    public function testGetTableNames(): void
    {
        $this->assertEquals(['table1', 'query1'], $this->dataSet->getTableNames());
    }

    public function testCreateIterator(): void
    {
        $expectedTable1 = $this->getConnection()->createDataSet(['table1'])->getTable('table1');

        $expectedTable2 = new DefaultTable(
            new DefaultTableMetadata('query1', ['tc1', 'tc2'])
        );

        $expectedTable2->addRow([
            'tc1' => 'bar',
            'tc2' => 'blah',
        ]);

        foreach ($this->dataSet as $i => $table) {
            switch ($table->getTableMetaData()->getTableName()) {
                case 'table1':
                    self::assertTablesEqual($expectedTable1, $table);
                    break;
                case 'query1':
                    self::assertTablesEqual($expectedTable2, $table);
                    break;
                default:
                    $this->fail('Proper keys not present from the iterator');
            }
        }
    }

    /**
     * @return DefaultConnection
     */
    protected function getConnection(): DefaultConnection
    {
        return $this->createDefaultDBConnection($this->pdo, 'test');
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(TEST_FILES_PATH . 'XmlDataSets/QueryDataSetTest.xml');
    }
}
