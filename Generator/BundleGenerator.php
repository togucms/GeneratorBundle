<?php

/*
 * Copyright (c) 2012-2014 Alessandro Siragusa <alessandro@togu.io>
 *
 * This file is part of the Togu CMS.
 *
 * Togu is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Togu is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Togu.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Togu\GeneratorBundle\Generator;

use Sonata\EasyExtendsBundle\Generator\BundleGenerator as BaseBundleGenerator;

use Symfony\Component\Console\Output\OutputInterface;
use Sonata\EasyExtendsBundle\Bundle\BundleMetadata;

class BundleGenerator extends BaseBundleGenerator
{

    /**
     * {@inheritdoc}
     */
    protected function generateBundleDirectory(OutputInterface $output, BundleMetadata $bundleMetadata)
    {
        $directories = $this->getDirectories();

        foreach ($directories as $directory) {
            $dir = sprintf('%s/%s', $bundleMetadata->getExtendedDirectory(), $directory);
            if (!is_dir($dir)) {
                $output->writeln(sprintf('  > generating bundle directory <comment>%s</comment>', $dir));
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * @return array
     */
    protected function getDirectories() {
    	return array(
            '',
            'Resources/config/routing',
            'Resources/views',
            'DependencyInjection',
            'Document',
            'Document/App',
            'Document/App/Generated',
    		'Controller'
        );
    }
}
