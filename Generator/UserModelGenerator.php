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
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * Generates a Doctrine PHPCR entity class based on its name, fields and format.
 *
 */
class UserModelGenerator extends Generator
{
	private $filesystem;
	private $modelLoader;

	/**
	 *
	 * @param Filesystem $filesystem
	 */
	public function __construct(Filesystem $filesystem, $modelLoader)
	{
		$this->filesystem = $filesystem;
		$this->modelLoader = $modelLoader;
	}

	/**
	 *
	 * @param BundleInterface $bundle
	 * @param string $modelName
	 * @param boolean $overwrite
	 */
    public function generate(BundleInterface $bundle, $modelName, $overwrite = false)
    {
    	$className = $this->modelLoader->getClassName($modelName);

        $documentPath = $bundle->getPath() . '/Document/App/' . $className . '.php';
        $traitPath = $bundle->getPath() . '/Document/App/Generated/' . $className . '.php';

        $params = $this->getClassConfig($modelName);

        if ($overwrite || ! file_exists($documentPath)) {
        	$this->renderFile('Document.php.twig', $documentPath, $params);
        }

        $this->renderFile('Trait.php.twig', $traitPath, $params);
    }

    /**
     *
     * @param string $modelName
     * @param string $modelConfig
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function getClassConfig($modelName) {
    	$className = $this->modelLoader->getClassName($modelName);
    	$modelConfig = $this->modelLoader->getConfig($modelName);

    	$extends = 'Model';
    	if(isset($modelConfig['extends'])) {
    		$extends = $this->modelLoader->getClassName($modelConfig['extends']);
    	}

    	$description = '';
    	if(isset($modelConfig['description'])) {
    		$description = $modelConfig['description'];
    	}

    	$translator = 'attribute';
    	if(isset($modelConfig['translator'])) {
    		$translator = $modelConfig['translator'];
    	}

    	$fields = array();
    	if(isset($modelConfig['fields'])) {
    		foreach ($modelConfig['fields'] as $fieldName => $field) {
    			if(! isset($field['model'])) {
    				throw new \InvalidArgumentException(sprintf('Field model has not been defined for field % of model %s', $fieldName, $modelName));
    			}
    			try {
    				$fields[] = $this->getFieldConfig($fieldName, $field['model']);
    			} catch (\InvalidArgumentException $e) {
    				throw new \InvalidArgumentException(sprintf('The field type has not been defined for the field % of model %'), $fieldName, $modelName);
    			}
    		}
    	}

    	if(isset($modelConfig['section'])) {
    		if(! isset($modelConfig['section']['leaf'])) {
    			throw new \InvalidArgumentException(sprintf('The leaf parameter must be specified for the model %s', $modelName));
    		}
    		$fields[] = $this->getFieldConfig('sectionConfig', array('type' => 'sectionConfig', 'serialize' => $modelName != "rootModel"));

    		if($modelConfig['section']['leaf'] === true) {
    			$fields[] = $this->getFieldConfig('link', array('type' => 'sectionLink', 'persist' => false));
    		}
    	}

    	if($modelName == "rootModel") {
    		$fields[] = $this->getFieldConfig('nodeName', array('type' => 'nodeName', 'defaultValue' => 'rootModel'));
    	}

    	$fields[] = $this->getFieldConfig('type', array('type' => 'type', 'defaultValue' => $modelName));

    	return array(
   			'className' => $className,
    		'namespace' => $this->modelLoader->getBaseNamespace(),
    		'baseModelClass' => $this->getBaseModelClass(),
    		'extJsModel' => $this->modelLoader->getExtJSClassName($modelName),
    		'extends' => $extends,
   			'fields' => $fields,
    		'description' => $description,
    		'modelName' => $modelName,
    		'translator' => $translator
    	);
    }

    /**
     *
     * @return string
     */
    protected function getBaseModelClass() {
    	return 'Application\Togu\ApplicationModelsBundle\Document\Model';
    }

    /**
     *
     * @param string $fieldName
     * @param string $fieldModel
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function getFieldConfig($fieldName, $fieldModel) {
    	if(! isset($fieldModel['type'])) {
    		throw new \InvalidArgumentException();
    	}
    	return array(
    		'fieldName' => $fieldName,
    		'ucFirstFieldName' => ucfirst($fieldName),
    		'defaultValue' => var_export(isset($fieldModel['defaultValue']) ? $fieldModel['defaultValue'] : null, true),
    		'persist' => ! isset($fieldModel['persist']) || $fieldModel['persist'] !== false,
    		'type' => $fieldModel['type'],
    		'tpl' => 'fields/' . strtolower($fieldModel['type']) . '.php.twig',
    		'translated' => isset($fieldModel['translated']) && $fieldModel['translated'] !== false,
    		'nullable' => isset($fieldModel['nullable']) && $fieldModel['nullable'] === true,
    		'serialize' => isset($fieldModel['serialize']) && $fieldModel['serialize'] !== false
    	);
    }

}
