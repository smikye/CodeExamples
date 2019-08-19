<?php

namespace Shape\InputFilter;

use Shape\InputFilter\InputFilter;
use Shape\Resource\Color;
use Shape\Validator\IsIntegerValidator;
use Shape\Filter\ShapeFilter;
use Shape\DBAL\Types\EnumShapeType;
use Zend\Filter\FilterInterface;
use Zend\InputFilter\ArrayInput;
use Zend\InputFilter\InputFilterInterface;
use Zend\I18n\Validator\IsFloat;
use Zend\Validator\InArray;

class ShapeInputFilter extends InputFilter
{
    const CIRCLE_VALIDATION_GROUP = [
        'shapeType',
        'x',
        'y',
        'radius',
        'fill',
        'stroke',
        'strokeWidth'
    ];

    const RECTANGLE_VALIDATION_GROUP = [
        'shapeType',
        'x',
        'y',
        'width',
        'height',
        'fill',
        'stroke',
        'strokeWidth'
    ];

    const CLOUD_VALIDATION_GROUP = [
        'shapeType',
        'x',
        'y',
        'points',
        'stroke',
        'strokeWidth'
    ];

    const DEFAULT_VALIDATION_GROUP = [
        'shapeType'
    ];

    /**
     * @param array|null|\Traversable $data
     * @return InputFilterInterface
     */
    public function setData($data)
    {
        $validationGroup = [];
        if (!isset($data['shapeType'])) {
            $validationGroup = self::DEFAULT_VALIDATION_GROUP;
        } else {
            switch ($data['shapeType']) {
                case 'circle':
                    $validationGroup = self::CIRCLE_VALIDATION_GROUP;
                    break;
                case 'rectangle':
                    $validationGroup = self::RECTANGLE_VALIDATION_GROUP;
                    break;
                case 'cloud':
                    $validationGroup = self::CLOUD_VALIDATION_GROUP;
                    break;
            }
        }

        parent::setValidationGroup($validationGroup);
        return parent::setData($data);
    }

    /**
     * @return array|bool|mixed
     */
    public function getValues()
    {
        if (! parent::isValid()) {
            return false;
        }
        $filter = $this->getFilter();
        $values = parent::getValues();
        $values = $filter->filter($values);

        return $values;
    }

    /**
     * @return array
     */
    static public function getConfig()
    {
        // TODO: rename shapeType to type
        return [
            'shapeType' => [
                'name' => 'shapeType',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => EnumShapeType::getValues(),
                            'recursive' => true,
                            'messages' => [
                                InArray::NOT_IN_ARRAY => sprintf(
                                    "The value '%s' is not a valid shape type",
                                    '%value%'
                                )
                            ]
                        ]
                    ]
                ]
            ],
            'x' => [
                'name' => 'x',
                'required' => true,
                'validators' => [
                    [
                        'name'=> IsFloat::class
                    ]
                ]
            ],
            'y' => [
                'name' => 'y',
                'required' => true,
                'validators' => [
                    [
                        'name'=> IsFloat::class
                    ]
                ]
            ],
            'radius' => [
                'name' => 'radius',
                'required' => true,
                'validators' => [
                    [
                        'name'=> IsFloat::class
                    ]
                ]
            ],
            'width' => [
                'name' => 'width',
                'required' => true,
                'validators' => [
                    [
                        'name'=> IsFloat::class
                    ]
                ]
            ],
            'height' => [
                'name' => 'height',
                'required' => true,
                'validators' => [
                    [
                        'name'=> IsFloat::class
                    ]
                ]
            ],
            'points' => [
                'name' => 'points',
                'required' => true,
                'validators' => [
                    [
                        'name'=> IsIntegerValidator::class
                    ]
                ],
                'type' => ArrayInput::class
            ],
            'fill' => [
                'name' => 'fill',
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => array_map('strtolower', Color::getColorNames()),
                            'recursive' => true,
                            'messages' => [
                                InArray::NOT_IN_ARRAY => sprintf(
                                    "The value '%s' is not a valid '%s'",
                                    '%value%',
                                    'color name'
                                )
                            ]
                        ]
                    ]
                ]
            ],
            'stroke' => [
                'name' => 'stroke',
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => array_map('strtolower', Color::getColorNames()),
                            'recursive' => true,
                            'messages' => [
                                InArray::NOT_IN_ARRAY => sprintf(
                                    "The value '%s' is not a valid '%s'",
                                    '%value%',
                                    'color name'
                                )
                            ]
                        ]
                    ]
                ]
            ],
            'strokeWidth' => [
                'name' => 'strokeWidth',
                'required' => false,
                'validators' => [
                    [
                        'name'=> IsFloat::class
                    ]
                ]
            ],
            'type' => self::class
        ];
    }

    /**
     * @return FilterInterface
     */
    protected function getFilter()
    {
        return $this->factory->getDefaultFilterChain()->getPluginManager()->get(ShapeFilter::class);
    }
}
