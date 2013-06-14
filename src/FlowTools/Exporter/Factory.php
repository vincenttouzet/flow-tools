<?php

/*
* This file is part of the Flow Tools utility.
*
* (c) Vincent Touzet <vincent.touzet@gmail.com>
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace FlowTools\Exporter;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Exporter\Source\CsvSourceIterator;
use Exporter\Source\XmlSourceIterator;
use Exporter\Source\XmlExcelSourceIterator;
use Exporter\Writer\CsvWriter;
use Exporter\Writer\JsonWriter;
use Exporter\Writer\XlsWriter;
use Exporter\Writer\XmlWriter;
use Exporter\Writer\XmlExcelWriter;

class Factory
{
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function getOption($opt)
    {
        return $this->options[$opt];
    }

    /**
     * Set the default options
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(
            array(
                'type',
                'direction',
                'filename'
            )
        );

        $resolver->setDefaults(
            array(
                'headers' => true,
                // csv options
                'delimiter' => ',',
                'enclosure' => '"',
                'escape' => '\\',
                // xml
                'main_element' => 'datas',
                'child_element' => 'data',
                'columns_type' => null,
            )
        );
    }

    public function createSource()
    {
        $source = null;
        switch ($this->options['type']) {
            case 'csv':
                $source = new CsvSourceIterator(
                    $this->options['filename'],
                    $this->options['delimiter'],
                    $this->options['enclosure'],
                    $this->options['escape'],
                    $this->options['headers']
                );
                break;
            case 'xml':
                $source = new XmlSourceIterator(
                    $this->options['filename']
                );
                break;
            case 'excel':
                $source = new XmlExcelSourceIterator(
                    $this->options['filename'],
                    $this->options['headers']
                );
                break;
        }
        if (is_null($source)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The type "%s" for source does not exist.',
                    $this->options['type']
                )
            );
        }
        return $source;
    }

    public function createWriter()
    {
        switch ($this->options['type']) {
            case 'csv':
                $writer = new CsvWriter(
                    $this->options['filename'],
                    $this->options['delimiter'],
                    $this->options['enclosure'],
                    $this->options['escape'],
                    $this->options['headers']
                );
                break;
            case 'xml':
                $writer = new XmlWriter(
                    $this->options['filename'],
                    $this->options['main_element'],
                    $this->options['child_element']
                );
                break;
            case 'excel':
                $writer = new XmlExcelWriter(
                    $this->options['filename'],
                    $this->options['headers'],
                    $this->options['columns_type']
                );
                break;
            case 'json':
                $writer = new JsonWriter(
                    $this->options['filename']
                );
                break;
            case 'xls':
                $writer = new XlsWriter(
                    $this->options['filename'],
                    $this->options['headers']
                );
                break;
        }
        if (is_null($writer)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The type "%s" for writer does not exist.',
                    $this->options['type']
                )
            );
        }
        return $writer;
    }

    public static function create(array $options = array())
    {
        $factory = new Factory($options);
        $obj = null;
        switch ($factory->getOption('direction')) {
            case 'in':
                $obj = $factory->createSource();
                break;
            case 'out':
                $obj = $factory->createWriter();
                break;
        }
        if (is_null($obj)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The direction "%s" does not exist. Valid direction are [in, out]',
                    $factory->getOption('direction')
                )
            );
        }
        return $obj;
    }
}
