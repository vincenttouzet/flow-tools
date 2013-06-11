<?php

/*
* This file is part of the Flow Tools utility.
*
* (c) Vincent Touzet <vincent.touzet@gmail.com>
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace FlowTools\Util;

use Symfony\Component\Finder\Finder;

class Compiler
{
    public function compile($pharFile = 'flow-tools.phar')
    {        
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'flow-tools.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        // CLI Component files
        foreach ($this->getFiles() as $file) {
            $path = str_replace(__DIR__.'/', '', $file);
            $phar->addFromString($path, file_get_contents($file));
        }
        $this->addFlowTools($phar);

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        // $phar->compressFiles(\Phar::GZ);

        unset($phar);

        chmod($pharFile, 0777);
    }

    /**
* Remove the shebang from the file before add it to the PHAR file.
*
* @param \Phar $phar PHAR instance
*/
    protected function addFlowTools(\Phar $phar)
    {
        $content = file_get_contents(__DIR__ . '/../../../flow-tools');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);

        $phar->addFromString('flow-tools', $content);
    }

    protected function getStub()
    {
        return "#!/usr/bin/env php\n<?php Phar::mapPhar('flow-tools.phar'); require 'phar://flow-tools.phar/flow-tools'; __HALT_COMPILER();";
    }

    protected function getLicense()
    {
        return '
/*
* This file is part of the Flow Tools utility.
*
* (c) Vincent Touzet <vincent.touzet@gmail.com>
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/';
    }

    protected function getFiles()
    {
        $iterator = Finder::create()->files()->exclude('Tests')->name('*.php')->in(array('vendor', 'src'));

        return array_merge(array('LICENSE'), iterator_to_array($iterator));
    }
}