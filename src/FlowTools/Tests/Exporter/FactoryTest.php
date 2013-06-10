<?php

namespace FlowTools\Tests\Exporter;

use FlowTools\Exporter\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateCsvSource()
    {
        $source = Factory::create(array(
            'direction' => 'in',
            'type' => 'csv',
            'filename' => 'foobar.csv'
        ));
        $this->assertEquals('Exporter\Source\CsvSourceIterator', get_class($source));
    }

    public function testCreateXmlSource()
    {
        $source = Factory::create(array(
            'direction' => 'in',
            'type' => 'xml',
            'filename' => 'foobar.csv'
        ));
        $this->assertEquals('Exporter\Source\XmlSourceIterator', get_class($source));
    }

    public function testCreateXmlExcelSource()
    {
        $source = Factory::create(array(
            'direction' => 'in',
            'type' => 'excel',
            'filename' => 'foobar.csv'
        ));
        $this->assertEquals('Exporter\Source\XmlExcelSourceIterator', get_class($source));
    }

    public function testCreateCsvWriter()
    {
        $writer = Factory::create(array(
            'direction' => 'out',
            'type' => 'csv',
            'filename' => 'foobar.csv'
        ));
        $this->assertEquals('Exporter\Writer\CsvWriter', get_class($writer));
    }

    public function testCreateXmlWriter()
    {
        $writer = Factory::create(array(
            'direction' => 'out',
            'type' => 'xml',
            'filename' => 'foobar.csv'
        ));
        $this->assertEquals('Exporter\Writer\XmlWriter', get_class($writer));
    }

    public function testCreateXmlExcelWriter()
    {
        $writer = Factory::create(array(
            'direction' => 'out',
            'type' => 'excel',
            'filename' => 'foobar.csv'
        ));
        $this->assertEquals('Exporter\Writer\XmlExcelWriter', get_class($writer));
    }

    public function testCreateJsonWriter()
    {
        $writer = Factory::create(array(
            'direction' => 'out',
            'type' => 'json',
            'filename' => 'foobar.csv'
        ));
        $this->assertEquals('Exporter\Writer\JsonWriter', get_class($writer));
    }

    public function testCreateXlsWriter()
    {
        $writer = Factory::create(array(
            'direction' => 'out',
            'type' => 'xls',
            'filename' => 'foobar.csv'
        ));
        $this->assertEquals('Exporter\Writer\XlsWriter', get_class($writer));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateSourceError()
    {
        $source = Factory::create(array(
            'direction' => 'in',
            'type' => 'unrecognized_type',
            'filename' => 'foobar.csv'
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWriterError()
    {
        $writer = Factory::create(array(
            'direction' => 'out',
            'type' => 'unrecognized_type',
            'filename' => 'foobar.csv'
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateError()
    {
        $writer = Factory::create(array(
            'direction' => 'unrecognized_direction',
            'type' => 'csv',
            'filename' => 'foobar.csv'
        ));
    }
}
