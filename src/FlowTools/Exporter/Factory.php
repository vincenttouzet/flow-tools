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
        switch ($this->options['type']) {
            case 'csv':
                return new CsvSourceIterator(
                    $this->options['filename'],
                    $this->options['delimiter'],
                    $this->options['enclosure'],
                    $this->options['escape'],
                    $this->options['headers']
                );
                break;
            case 'xml':
                return new XmlSourceIterator(
                    $this->options['filename']
                );
                break;
            case 'excel':
                return new XmlExcelSourceIterator(
                    $this->options['filename'],
                    $this->options['headers']
                );
                break;
        }
        throw new \InvalidArgumentException(sprintf('The type "%s" for source does not exist.', $this->options['type']));
    }

    public function createWriter()
    {
        switch ($this->options['type']) {
            case 'csv':
                return new CsvWriter(
                    $this->options['filename'],
                    $this->options['delimiter'],
                    $this->options['enclosure'],
                    $this->options['escape'],
                    $this->options['headers']
                );
                break;
            case 'xml':
                return new XmlWriter(
                    $this->options['filename'],
                    $this->options['main_element'],
                    $this->options['child_element']
                );
                break;
            case 'excel':
                return new XmlExcelWriter(
                    $this->options['filename'],
                    $this->options['headers'],
                    $this->options['columns_type']
                );
                break;
            case 'json':
                return new JsonWriter(
                    $this->options['filename']
                );
                break;
            case 'xls':
                return new XlsWriter(
                    $this->options['filename'],
                    $this->options['headers']
                );
                break;
        }
        throw new \InvalidArgumentException(sprintf('The type "%s" for writer does not exist.', $this->options['type']));
    }

    public static function create(array $options = array())
    {
        $factory = new Factory($options);
        switch ($factory->getOption('direction')) {
            case 'in':
                return $factory->createSource();
                break;
            case 'out':
                return $factory->createWriter();
                break;
        }
        throw new \InvalidArgumentException(sprintf('The direction "%s" does not exist. Valid direction are [in, out]', $factory->getOption('direction')));
    }
}
