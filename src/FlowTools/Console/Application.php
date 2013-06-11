<?php

/*
* This file is part of the Flow Tools utility.
*
* (c) Vincent Touzet <vincent.touzet@gmail.com>
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace FlowTools\Console;

use Symfony\Component\Console\Application as BaseApplication;
use FlowTools\Console\Command\ConvertCommand;
use FlowTools\Console\Command\CompileCommand;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Flow Tools', '0.1-DEV');

        $this->add(new ConvertCommand());
        $this->add(new CompileCommand());
    }

    public function getLongVersion()
    {
        return parent::getLongVersion().' by <comment>Vincent Touzet</comment>';
    }
}
