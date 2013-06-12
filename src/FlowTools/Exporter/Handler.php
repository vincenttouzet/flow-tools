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

use Exporter\Handler as BaseHandler;
use Exporter\Source\SourceIteratorInterface;
use Exporter\Writer\WriterInterface;

class Handler extends BaseHandler
{
    protected $nbEntries = 0;

    /**
     * @return void
     */
    public function export()
    {
        $this->writer->open();

        foreach ($this->source as $data) {
            $this->writer->write($data);
            $this->nbEntries++;
        }

        $this->writer->close();
    }

    /**
     * Get the number of rows exported
     *
     * @return integer
     */
    public function getNbEntries()
    {
        return $this->nbEntries;
    }

    /**
     * @static
     *
     * @param Exporter\Source\SourceIteratorInterface $source
     * @param Exporter\Writer\WriterInterface         $writer
     *
     * @return Handler
     */
    public static function create(SourceIteratorInterface $source, WriterInterface $writer)
    {
        // use static instead of Handler
        return new static($source, $writer);
    }
}
