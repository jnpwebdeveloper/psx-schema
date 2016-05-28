<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2016 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PSX\Schema\Property;

use PSX\Record\RecordInterface;
use PSX\Schema\ChoiceResolver;
use PSX\Schema\ChoiceResolverInterface;
use PSX\Schema\PropertyInterface;
use PSX\Schema\Property;
use RuntimeException;
use ReflectionClass;

/**
 * ChoiceType
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ChoiceType extends CompositeTypeAbstract
{
    /**
     * @var \PSX\Schema\ChoiceResolverInterface
     */
    protected $choiceResolver;

    public function add(PropertyInterface $property)
    {
        if (!$property instanceof ComplexType && !$property instanceof RecursionType) {
            throw new RuntimeException('Choice property accepts only complex types ' . get_class($property). ' given');
        }

        $this->properties[] = $property;

        return $this;
    }

    public function setResolver(ChoiceResolverInterface $choiceResolver)
    {
        $this->choiceResolver = $choiceResolver;
    }

    public function getChoice($data, $path)
    {
        return $this->getResolver()->getProperty($data, $path, $this);
    }

    public function getChoiceTypes()
    {
        return $this->getResolver()->getTypes($this);
    }
    
    protected function getResolver()
    {
        static $resolver;

        if ($resolver) {
            return $resolver;
        }

        if ($this->choiceResolver === null) {
            if (!empty($this->reference)) {
                // if we have a reference it must be a choice resolver class
                $class = new ReflectionClass($this->reference);
                if ($class->implementsInterface('PSX\\Schema\\ChoiceResolverInterface')) {
                    $resolver = $class->newInstance();
                } else {
                    throw new RuntimeException('Choice type reference must implement the interface PSX\\Schema\\ChoiceResolverInterface');
                }
            } else {
                $resolver = new ChoiceResolver();
            }
        } else {
            $resolver = $this->choiceResolver;
        }

        return $resolver;
    }
}
