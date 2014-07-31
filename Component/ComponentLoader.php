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

namespace Togu\GeneratorBundle\Component;

use Togu\GeneratorBundle\Component\ComponentLoaderInterface;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class ComponentLoader implements ComponentLoaderInterface {
	protected $directory;
	protected $configFile;
	protected $components = null;

	/**
	 * @param string $directory
	 * @param string $configFile
	 */
	public function __construct($directory, $configFile) {
		$this->directory = $directory;
		$this->configFile = $configFile;
	}

	protected function loadComponents() {
		if($this->components === null) {
			$locator = new FileLocator($this->directory);
			$this->components = Yaml::parse(file_get_contents($locator->locate($this->configFile)));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConfig($componentName) {
		$this->loadComponents();
		return  $this->components[$componentName];
	}

	public function getComponents() {
		$this->loadComponents();
		return array_keys($this->components);
	}
}