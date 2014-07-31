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

namespace Togu\GeneratorBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;

use Togu\GeneratorBundle\Bundle\BundleMetadata;
use Togu\GeneratorBundle\Generator\UserModelGenerator;


class GenerateModelsCommand extends GeneratorCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {

        $this
            ->setName('togu:models:generate')
            ->setHelp(<<<EOT
The <info>togu:models:generate</info> command generates the symfony entities for Togu.
EOT
        );

        $this->setDescription('Generates the entities used by Togu');

        $this->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'If set this flag will overwrite the base classes');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$bundle = $this->getContainer()->get('kernel')->getBundle('ApplicationToguApplicationModelsBundle');

    	$generator = $this->getGenerator();

    	$modelLoader = $this->getContainer()->get('togu.generator.model.config');

    	$overwrite = !! $input->getOption('overwrite');

    	foreach($modelLoader->getModels() as $modelName) {
    		$output->write(sprintf('Generating entity %s ... ', $modelName));
    		$generator->generate($bundle, $modelName, $overwrite);
    		$output->writeln('done');
    	}

        return 0;
    }

    protected function getSkeletonDirs(BundleInterface $bundle = null) {
    	return array(
    		__DIR__.'/../Resources/skeleton/document',
    	);
	}

    protected function createGenerator() {
    	return new UserModelGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('togu.generator.model.config'));
    }
}
