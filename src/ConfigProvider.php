<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 11/16/2016
 * Time: 4:26 PM
 */

namespace Dot\Ems;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),

            'dot_ems' => [

            ],
        ];
    }

    public function getDependencyConfig()
    {
        return [

        ];
    }
}
