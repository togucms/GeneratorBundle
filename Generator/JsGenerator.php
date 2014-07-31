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

use Symfony\Component\Filesystem\Filesystem;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * Generates a Doctrine PHPCR entity class based on its name, fields and format.
 *
 */
class JsGenerator extends Generator
{
	private $filesystem;

	public function __construct(Filesystem $filesystem)
	{
		$this->filesystem = $filesystem;
	}

    public function generate($basePath, $modelLoader, $componentLoader)
    {
        $modelsPath = $basePath . '/ModelTypes.js';
        $componentsPath = $basePath . '/ComponentTypes.js';

        $params = array( 'json' => json_encode($this->getModelConfig($modelLoader)));
        $this->renderFile('ModelTypes.js.twig', $modelsPath, $params);

        $params = array( 'json' => json_encode($this->getComponentConfig($componentLoader)));
        $this->renderFile('ComponentTypes.js.twig', $componentsPath, $params);
    }

    protected function getModelConfig($modelLoader) {
    	$config = array();
    	foreach($modelLoader->getModels() as $modelName) {
    		$modelConfig = $modelLoader->getConfig($modelName);
    		$model = array(
    			'className' => $modelConfig['className'],
    			'fields' => $this->getFieldConfig($modelConfig)
    		);
    		if($modelName == "rootModel") {
    			$model['fields'][] = array(
    				'name' => 'page',
    				'type' => 'page',
					'defaultValue' => null
    			);
    		}

    		if(isset($modelConfig['extends'])) {
    			$model['extend'] = $modelConfig['extends'];
    		}

    		$config[$modelName] = $model;
    	}

    	return $config;
    }

    protected function getFieldConfig($modelConfig) {
    	$fieldConfig = array();

    	if(isset($modelConfig['section']) && $modelConfig['section']['leaf'] === false) {
    		if(! isset($modelConfig['fields'])) {
    			$modelConfig['fields'] = array();
    		}
    		$modelConfig['fields']['nextSection'] = array(
    			'model' => array(
   					'type' => 'reference',
   					'persist' => false,
   					'defaultValue' => array()
   				)
    		);
    	}

    	if(! isset($modelConfig['fields'])) {
    		return $fieldConfig;
    	}

    	foreach ($modelConfig['fields'] as $fieldName => $fieldData) {
    		if(! isset($fieldData['model'])) {
    			throw new \InvalidArgumentException(sprintf('The field %s needs a model definition', $fieldName));
    		}
    		$fieldConfig[] = array(
    			'name' => $fieldName,
    			'type' => $fieldData['model']['type'],
    			'defaultValue' => $fieldData['model']['defaultValue']
    		);
    	}
    	return $fieldConfig;
    }

    protected function getComponentConfig($componentLoader) {
    	$config = array();
    	foreach($componentLoader->getComponents() as $componentName) {
    		$componentConfig = $componentLoader->getConfig($componentName);
    		$config[$componentName] = array(
    			'className' => $componentConfig['className'],
    			'template' => $componentConfig['template'],
   				'containerConfig' => $this->getContainers($componentConfig)
    		);
    	}

    	return $config;
    }

    protected function getContainers($componentConfig) {
    	$containers = array();
    	if(! isset($componentConfig['containers'])) {
    		return $containers;
    	}
    	foreach ($componentConfig['containers'] as $name => $container) {
    		$container['name'] = $name;
    		$containers[] = $container;
    	}
    	return $containers;
    }


}
