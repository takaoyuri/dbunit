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

use PHPUnit\DbUnit\DataSet\DefaultTable;
use PHPUnit\DbUnit\DataSet\DefaultTableMetadata;
use PHPUnit\DbUnit\DataSet\ITable;
use PHPUnit\DbUnit\DataSet\ITableMetadata;
use PHPUnit\DbUnit\DataSet\QueryTable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractTableTest extends TestCase
{
    /**
     * @var QueryTable
     */
    protected $table;

    protected function setUp(): void
    {
        $tableMetaData = new DefaultTableMetadata(
            'table',
            ['id', 'column1']
        );

        $this->table = new DefaultTable($tableMetaData);

        $this->table->addRow([
            'id' => 1,
            'column1' => 'randomValue',
        ]);
    }

    /**
     * @param array $row
     * @param bool $exists
     */
    #[DataProvider('providerTableContainsRow')]
    public function testTableContainsRow($row, $exists): void
    {
        $result = $this->table->assertContainsRow($row);
        $this->assertEquals($exists, $result);
    }

    public static function providerTableContainsRow(): array
    {
        return [
            [[
                'id' => 1,
                'column1' => 'randomValue',
            ], true],
            [[
                'id' => 1,
                'column1' => 'notExistingValue',
            ], false],
        ];
    }

    public function testMatchesWithNonMatchingMetaData(): void
    {
        $tableMetaData = $this->createMock(ITableMetadata::class);
        $otherMetaData = $this->createMock(ITableMetadata::class);

        $otherTable = $this->createMock(ITable::class);
        $otherTable->expects($this->once())
            ->method('getTableMetaData')
            ->willReturn($otherMetaData);

        $tableMetaData->expects($this->once())
            ->method('matches')
            ->with($otherMetaData)
            ->willReturn(false);

        $table = new DefaultTable($tableMetaData);
        $this->assertFalse($table->matches($otherTable));
    }

    public function testMatchesWithNonMatchingRowCount(): void
    {
        $tableMetaData = $this->createMock(ITableMetadata::class);
        $otherMetaData = $this->createMock(ITableMetadata::class);
        $otherTable = $this->createMock(ITable::class);

        /** @var MockObject|DefaultTable $table */
        $table = $this->getMockBuilder(DefaultTable::class)
            ->setConstructorArgs([$tableMetaData])
            ->onlyMethods(['getRowCount'])
            ->getMock();

        $otherTable->expects($this->once())
            ->method('getTableMetaData')
            ->willReturn($otherMetaData);
        $otherTable->expects($this->once())
            ->method('getRowCount')
            ->willReturn(0);

        $tableMetaData->expects($this->once())
            ->method('matches')
            ->with($otherMetaData)
            ->willReturn(true);

        $table->expects($this->once())
            ->method('getRowCount')
            ->willReturn(1);

        $this->assertFalse($table->matches($otherTable));
    }

    /**
     * @param array $tableColumnValues
     * @param array $otherColumnValues
     * @param bool $matches
     */
    #[DataProvider('providerMatchesWithColumnValueComparisons')]
    public function testMatchesWithColumnValueComparisons($tableColumnValues, $otherColumnValues, $matches): void
    {
        $tableMetaData = $this->createMock(ITableMetadata::class);
        $otherMetaData = $this->createMock(ITableMetadata::class);
        $otherTable = $this->createMock(ITable::class);

        /** @var MockObject|DefaultTable $table */
        $table = $this->getMockBuilder(DefaultTable::class)
            ->setConstructorArgs([$tableMetaData])
            ->onlyMethods(['getRowCount', 'getValue'])
            ->getMock();

        $otherTable->expects($this->once())
            ->method('getTableMetaData')
            ->willReturn($otherMetaData);
        $otherTable->expects($this->once())
            ->method('getRowCount')
            ->willReturn(\count($otherColumnValues));

        $tableMetaData->expects($this->once())
            ->method('getColumns')
            ->willReturn(array_keys(reset($tableColumnValues)));
        $tableMetaData->expects($this->once())
            ->method('matches')
            ->with($otherMetaData)
            ->willReturn(true);

        $table
            ->method('getRowCount')
            ->willReturn(\count($tableColumnValues));

        $tableMap = [];
        $otherMap = [];

        foreach ($tableColumnValues as $rowIndex => $rowData) {
            foreach ($rowData as $columnName => $columnValue) {
                $tableMap[] = [$rowIndex, $columnName, $columnValue];
                $otherMap[] = [$rowIndex, $columnName, $otherColumnValues[$rowIndex][$columnName]];
            }
        }
        $table
            ->method('getValue')
            ->willReturnMap($tableMap);
        $otherTable
            ->method('getValue')
            ->willReturnMap($otherMap);

        $this->assertSame($matches, $table->matches($otherTable));
    }

    public static function providerMatchesWithColumnValueComparisons(): array
    {
        return [

            // One row, one column, matches
            [
                [
                    [
                        'id' => 1,
                    ],
                ],
                [
                    [
                        'id' => 1,
                    ],
                ],
                true,
            ],

            // One row, one column, does not match
            [
                [
                    [
                        'id' => 1,
                    ],
                ],
                [
                    [
                        'id' => 2,
                    ],
                ],
                false,
            ],

            // Multiple rows, one column, matches
            [
                [
                    [
                        'id' => 1,
                    ],
                    [
                        'id' => 2,
                    ],
                ],
                [
                    [
                        'id' => 1,
                    ],
                    [
                        'id' => 2,
                    ],
                ],
                true,
            ],

            // Multiple rows, one column, do not match
            [
                [
                    [
                        'id' => 1,
                    ],
                    [
                        'id' => 2,
                    ],
                ],
                [
                    [
                        'id' => 1,
                    ],
                    [
                        'id' => 3,
                    ],
                ],
                false,
            ],

            // Multiple rows, multiple columns, matches
            [
                [
                    [
                        'id' => 1,
                        'name' => 'foo',
                    ],
                    [
                        'id' => 2,
                        'name' => 'bar',
                    ],
                ],
                [
                    [
                        'id' => 1,
                        'name' => 'foo',
                    ],
                    [
                        'id' => 2,
                        'name' => 'bar',
                    ],
                ],
                true,
            ],

            // Multiple rows, multiple columns, do not match
            [
                [
                    [
                        'id' => 1,
                        'name' => 'foo',
                    ],
                    [
                        'id' => 2,
                        'name' => 'bar',
                    ],
                ],
                [
                    [
                        'id' => 1,
                        'name' => 'foo',
                    ],
                    [
                        'id' => 2,
                        'name' => 'baz',
                    ],
                ],
                false,
            ],

            // Int and int as string must match
            [
                [
                    [
                        'id' => 42,
                    ],
                ],
                [
                    [
                        'id' => '42',
                    ],
                ],
                true,
            ],

            // Float and float as string must match
            [
                [
                    [
                        'id' => 15.3,
                    ],
                ],
                [
                    [
                        'id' => '15.3',
                    ],
                ],
                true,
            ],

            // Int and float must match
            [
                [
                    [
                        'id' => 18.00,
                    ],
                ],
                [
                    [
                        'id' => 18,
                    ],
                ],
                true,
            ],

            // 0 and empty string must not match
            [
                [
                    [
                        'id' => 0,
                    ],
                ],
                [
                    [
                        'id' => '',
                    ],
                ],
                false,
            ],

            // 0 and null must not match
            [
                [
                    [
                        'id' => 0,
                    ],
                ],
                [
                    [
                        'id' => null,
                    ],
                ],
                false,
            ],

            // empty string and null must not match
            [
                [
                    [
                        'id' => '',
                    ],
                ],
                [
                    [
                        'id' => null,
                    ],
                ],
                false,
            ],
        ];
    }
}
