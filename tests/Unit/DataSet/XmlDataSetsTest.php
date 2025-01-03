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

use PHPUnit\DbUnit\Constraint\DataSetIsEqual;
use PHPUnit\DbUnit\DataSet\DefaultDataSet;
use PHPUnit\DbUnit\DataSet\DefaultTable;
use PHPUnit\DbUnit\DataSet\DefaultTableMetadata;
use PHPUnit\DbUnit\DataSet\FlatXmlDataSet;
use PHPUnit\DbUnit\DataSet\MysqlXmlDataSet;
use PHPUnit\DbUnit\DataSet\XmlDataSet;
use PHPUnit\Framework\TestCase;

class XmlDataSetsTest extends TestCase
{
    protected $expectedDataSet;

    protected function setUp(): void
    {
        $table1MetaData = new DefaultTableMetadata(
            'table1',
            ['table1_id', 'column1', 'column2', 'column3', 'column4']
        );
        $table2MetaData = new DefaultTableMetadata(
            'table2',
            ['table2_id', 'column5', 'column6', 'column7', 'column8']
        );

        $table1 = new DefaultTable($table1MetaData);
        $table2 = new DefaultTable($table2MetaData);

        $table1->addRow([
            'table1_id' => 1,
            'column1' => 'tgfahgasdf',
            'column2' => 200,
            'column3' => 34.64,
            'column4' => 'yghkf;a  hahfg8ja h;',
        ]);
        $table1->addRow([
            'table1_id' => 2,
            'column1' => 'hk;afg',
            'column2' => 654,
            'column3' => 46.54,
            'column4' => '24rwehhads',
        ]);
        $table1->addRow([
            'table1_id' => 3,
            'column1' => 'ha;gyt',
            'column2' => 462,
            'column3' => 1654.4,
            'column4' => 'asfgklg',
        ]);

        $table2->addRow([
            'table2_id' => 1,
            'column5' => 'fhah',
            'column6' => 456,
            'column7' => 46.5,
            'column8' => 'fsdbghfdas',
        ]);
        $table2->addRow([
            'table2_id' => 2,
            'column5' => 'asdhfoih',
            'column6' => 654,
            'column7' => null,
            'column8' => '43asdfhgj',
        ]);
        $table2->addRow([
            'table2_id' => 3,
            'column5' => 'ajsdlkfguitah',
            'column6' => 654,
            'column7' => null,
            'column8' => null,
        ]);

        $this->expectedDataSet = new DefaultDataSet([$table1, $table2]);
    }

    public function testFlatXmlDataSet(): void
    {
        $constraint = new DataSetIsEqual($this->expectedDataSet);
        $xmlFlatDataSet = new FlatXmlDataSet(TEST_FILES_PATH . 'XmlDataSets/FlatXmlDataSet.xml');

        self::assertThat($xmlFlatDataSet, $constraint);
    }

    public function testXmlDataSet(): void
    {
        $constraint = new DataSetIsEqual($this->expectedDataSet);
        $xmlDataSet = new XmlDataSet(TEST_FILES_PATH . 'XmlDataSets/XmlDataSet.xml');

        self::assertThat($xmlDataSet, $constraint);
    }

    public function testMysqlXmlDataSet(): void
    {
        $constraint = new DataSetIsEqual($this->expectedDataSet);
        $mysqlXmlDataSet = new MysqlXmlDataSet(TEST_FILES_PATH . 'XmlDataSets/MysqlXmlDataSet.xml');

        self::assertThat($mysqlXmlDataSet, $constraint);
    }
}
