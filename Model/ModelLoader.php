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

namespace Togu\GeneratorBundle\Model;

use Togu\GeneratorBundle\Model\ModelLoaderInterface;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class ModelLoader implements ModelLoaderInterface {
	protected $directory;
	protected $configFile;
	protected $models = null;

	/**
	 * @param string $directory
	 * @param string $configFile
	 */
	public function __construct($directory, $configFile) {
		$this->directory = $directory;
		$this->configFile = $configFile;
	}

	protected function loadModels() {
		if($this->models === null) {
			$locator = new FileLocator($this->directory);
			$this->models = Yaml::parse(file_get_contents($locator->locate($this->configFile)));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConfig($modelName) {
		$this->loadModels();
		return $this->models[$modelName];
	}

	public function getModels() {
		$this->loadModels();
		return array_keys($this->models);
	}

	public function hasModel($modelName) {
		$this->loadModels();
		return isset($this->models[$modelName]);
	}


	/**
	 *
	 * @param string $modelName
	 * @return string
	 */
	public function getClassName($modelName) {
		return str_replace('/[\\\/]', '_', ucFirst($modelName));
	}

	/**
	 *
	 * @return string
	 */
	public function getBaseNamespace() {
		return 'Application\Togu\ApplicationModelsBundle\Document\App';
	}

	/**
	 *
	 * @param string $modelName
	 * @return string
	 */
	public function getFullClassName($modelName) {
		return $this->getBaseNamespace() . '\\' . $this->getClassName($modelName);
	}

	public function getExtJSClassName($modelName) {
		return "Togu.applicationModels." . $this->getClassName($modelName);
	}
}