<?php

/*
* This file is part of the Flow Tools utility.
*
* (c) Vincent Touzet <vincent.touzet@gmail.com>
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace FlowTools\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a helper to display a large table
 *
 * @author Vincent Touzet <vincent.touzet@gmail.com>
 */
class FullScreenTableHelper extends Helper
{
    protected $rowOffset = 0;
    protected $colOffset = 0;
    protected $maxColWidth = null;
    protected $moreColsText = '...';
    protected $colsWidth = array();
    protected $totalColsWidth = 0;

    protected $tableHelper = null;
    protected $headers = null;
    protected $rows = null;

    protected $screenSize = null;

    /**
     * @param TableHelper $tableHelper
     */
    public function __construct(TableHelper $tableHelper)
    {
        $this->tableHelper = $tableHelper;
    }

    /**
     * Render the table
     *
     * @param OutputInterface $output
     *
     * @return void
     */
    public function render(OutputInterface $output)
    {
        list($headers, $rows) = $this->computeTable();
        $this->tableHelper->setHeaders($headers);
        $this->tableHelper->setRows($rows);
        $this->tableHelper->render($output);
        if (count($this->rows) < $this->getNbRowsToShow()) {
            $diff = $this->getNbRowsToShow() - count($this->rows);
            for ($i=0; $i < $diff; $i++) {
                $output->writeln('');
            }
        }
    }

    /**
     * Retrieve the table to show
     * If there are too much columns, return a partial table
     *
     * @return array
     */
    private function computeTable()
    {
        $this->computeColsWidth();
        $width = $this->getScreenWidth();
        // if screen width > total columns width
        if ($width >= $this->totalColsWidth) {
            // show all table
            $headers = $this->headers;
            $rows = $this->rows;
        } else {
            $nbColsToShow = $this->computeNbColsToShow();
            $headers = array();
            if ($this->colOffset>0) {
                $headers[] = $this->moreColsText;
            }
            for ($i=$this->colOffset; $i<($this->colOffset+$nbColsToShow); $i++) {
                $headers[] = $this->headers[$i];
            }
            $headers[]=$this->moreColsText;
            $rows = array();
            for ($i=$this->rowOffset; $i < $this->rowOffset+min(count($this->rows), $this->getNbRowsToShow())-1; $i++) {
                $data = array();
                if ($this->colOffset>0) {
                    $data[] = $this->moreColsText;
                }
                for ($j=$this->colOffset; $j<($this->colOffset+$nbColsToShow); $j++) {
                    $value = $this->rows[$i][$this->headers[$j]];
                    if ($this->strlen($value) > $this->maxColWidth) {
                        $value = substr($value, 0, $this->maxColWidth-4).' ...';
                    }
                    $data[$this->headers[$j]] = $value;
                }
                $data[] = $this->moreColsText;
                $rows[] = $data;
            }
        }
        return array($headers, $rows);
    }

    /**
     * Calculate the number of columns to show
     *
     * @return [type]
     */
    private function computeNbColsToShow()
    {
        $screenWidth = $this->getScreenWidth();
        $width = 1;
        if ($this->colOffset > 0) {
            $width += $this->strlen($this->moreColsText)+3;
        }
        $width += $this->strlen($this->moreColsText)+3;
        $nbRows = 0;
        while ($screenWidth >= $width) {
            $width += $this->colsWidth[$this->headers[$this->colOffset+$nbRows]];
            if ($screenWidth > $width) {
                $nbRows++;
            }
        }
        if ($screenWidth < $width) {
            $nbRows--;
        }
        return $nbRows;
    }
    
    /**
     * Sets Headers
     * 
     * @param array $headers
     * 
     * @return FullScreenTableHelper
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }
    
    /**
     * Sets Rows
     * 
     * @param array $rows
     * 
     * @return FullScreenTableHelper
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * Gets RowOffset
     * 
     * @return integer
     */
    public function getRowOffset()
    {
        return $this->rowOffset;
    }
    
    /**
     * Sets RowOffset
     * 
     * @param integer $rowOffset
     * 
     * @return FullScreenTableHelper
     */
    public function setRowOffset($rowOffset)
    {
        $this->rowOffset = $rowOffset;
        return $this;
    }
    
    /**
     * Gets ColOffset
     * 
     * @return integer
     */
    public function getColOffset()
    {
        return $this->colOffset;
    }
    
    /**
     * Sets ColOffset
     * 
     * @param integer $colOffset
     * 
     * @return FullScreenTableHelper
     */
    public function setColOffset($colOffset)
    {
        $this->colOffset = $colOffset;
        return $this;
    }

    /**
     * Gets MaxColWidth
     * 
     * @return [type]
     */
    public function getMaxColWidth()
    {
        return $this->maxColWidth;
    }
    
    /**
     * Sets MaxColWidth
     * 
     * @param [type] $maxColWidth MaxColWidth
     * 
     * @return [type]
     */
    public function setMaxColWidth($maxColWidth)
    {
        $this->maxColWidth = (int)$maxColWidth;
        return $this;
    }

    /**
     * Gets text for the bound columns
     * 
     * @return string
     */
    public function getMoreColsText()
    {
        return $this->moreColsText;
    }
    
    /**
     * Sets text for the bound columns
     * 
     * @param string $moreColsText
     * 
     * @return FullScreenTableHelper
     */
    public function setMoreColsText($moreColsText)
    {
        $this->moreColsText = $moreColsText;
        return $this;
    }
    

    /**
     * Get the screen size
     *
     * @return array
     */
    private function getScreenSize()
    {
        preg_match_all("/rows.([0-9]+);.columns.([0-9]+);/", strtolower(exec('stty -a |grep columns')), $output);
        if (sizeof($output) == 3) {
            $this->screenSize['width'] = $output[2][0];
            $this->screenSize['height'] = $output[1][0];
        }
        return $this->screenSize;
    }

    /**
     * Gets the screen width
     *
     * @return integer
     */
    public function getScreenWidth()
    {
        if (is_null($this->screenSize)) {
            $this->getScreenSize();
        }
        return $this->screenSize['width'];
    }

    /**
     * Gets the screen height
     *
     * @return integer
     */
    public function getScreenHeight()
    {
        if (is_null($this->screenSize)) {
            $this->getScreenSize();
        }
        return $this->screenSize['height'];
    }

    public function getNbRowsToShow()
    {
        $nbRows = $this->getScreenHeight();
        $nbRowsToLoad = $nbRows - 4; // minus the header
        $nbRowsToLoad = floor($nbRowsToLoad);
        return $nbRowsToLoad;
    }

    /**
     * Compute the columns width
     *
     * @return void
     */
    private function computeColsWidth()
    {
        if (is_array($this->rows)) {
            $this->colsWidth = array();
            // init with headers length
            foreach ($this->headers as $header) {
                $len = $this->getLength($header);
                $this->colsWidth[$header] = $len;
            }
            // for each rows
            foreach ($this->rows as $row) {
                // for each columns
                foreach ($row as $header => $value) {
                    $len = $this->getLength($value);
                    if ($len > $this->colsWidth[$header]) {
                        $this->colsWidth[$header] = $len;
                    }
                }
            }
            $this->totalColsWidth = 0;
            foreach ($this->colsWidth as $key => $value) {
                $this->totalColsWidth += $value;
            }
            return $this->totalColsWidth + 1;
        }
    }

    /**
     * Gets the columns width for the given text
     *
     * @param string $text
     *
     * @return integer
     */
    private function getLength($text)
    {
        $len = $this->strlen($text)+3;
        if ($len > $this->maxColWidth) {
            $len = $this->maxColWidth;
        }
        return $len;
    }
    
    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'flowgrid';
    }
}
