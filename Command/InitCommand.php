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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;

use Togu\GeneratorBundle\Bundle\BundleMetadata;
use Togu\GeneratorBundle\Generator\UserModelGenerator;


class InitCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {

        $this
            ->setName('togu:init')
            ->setHelp(<<<EOT
The <info>togu:init</info> command generates the Application Bundle used by Togu.
EOT
        );

        $this->setDescription('Generates the Application Bundle used by Togu');

        $this->addOption('dest', 'd', InputOption::VALUE_OPTIONAL, 'The base folder where the Application will be created', false);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $destOption = $input->getOption('dest');
        if ($destOption) {
            $dest = realpath($destOption);
            if (false === $dest) {
                $output->writeln('');
                $output->writeln(sprintf('<error>The provided destination folder \'%s\' does not exist!</error>', $destOption));
                return 0;
            }
        } else {
            $dest = $this->getContainer()->get('kernel')->getRootDir();
        }

        $configuration = array(
            'application_dir' =>  sprintf("%s/Application", $dest)
        );

        $bundleName = 'ToguApplicationModelsBundle';

        $processed = false;
        foreach ($this->getContainer()->get('kernel')->getBundles() as $bundle) {

            if ($bundle->getName() != $bundleName) {
                continue;
            }

            $processed = true;
            $bundleMetadata = new BundleMetadata($bundle, $configuration);

            $output->writeln(sprintf('Processing bundle : "<info>%s</info>"', $bundleMetadata->getName()));

            $this->getContainer()->get('togu.generator.generator.bundle')
                ->generate($output, $bundleMetadata);

            $output->writeln(sprintf('Processing Doctrine PHPCR : "<info>%s</info>"', $bundleMetadata->getName()));
            $this->getContainer()->get('togu.generator.generator.phpcr')
                ->generate($output, $bundleMetadata);

/*            $output->writeln(sprintf('Processing Serializer config : "<info>%s</info>"', $bundleMetadata->getName()));
            $this->getContainer()->get('togu.generator.generator.serializer')
                ->generate($output, $bundleMetadata);
*/
            $output->writeln('');
        }

        if ($processed) {
        	$this->enableBundle($output, $this->getContainer()->get('kernel'), 'Application\\' . $bundleMetadata->getNamespace(),  'Application' . $bundleMetadata->getName());
            $output->writeln('done!');

            return 0;
        }

        $output->writeln(sprintf('<error>The bundle \'%s\' does not exist or not defined in the kernel file!</error>', $bundleName));

        return -1;
    }

    protected function enableBundle(OutputInterface $output, KernelInterface $kernel, $namespace, $bundle) {
    	$output->write('Enabling the bundle inside the Kernel: ');
    	$manip = new KernelManipulator($kernel);
    	try {
    		$ret = $manip->addBundle($namespace.'\\'.$bundle);
    	} catch (\RuntimeException $e) { }
    }
}
