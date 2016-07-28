<?php

namespace AppBundle\Doctrine\Orm\Filter;

use Doctrine\ORM\QueryBuilder;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Dunglas\ApiBundle\Doctrine\Orm\Filter\AbstractFilter;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class CollectionDateFilter extends AbstractFilter {
    const PARAMETER_BEFORE = 'before';
    const PARAMETER_AFTER = 'after';
    const EXCLUDE_NULL = 0;
    const INCLUDE_NULL_BEFORE = 1;
    const INCLUDE_NULL_AFTER = 2;

    private $assocName;

    /**
     * @var array
     */
    private static $doctrineDateTypes = [
        'date' => true,
        'datetime' => true,
        'datetimetz' => true,
        'time' => true,
    ];

    public function __construct(ManagerRegistry $managerRegistry, $assocName, array $properties = null) {
        $this->assocName = $assocName;
        parent::__construct($managerRegistry, $properties);
    }

    /**
     * Applies the filter.
     *
     * @param ResourceInterface $resource
     * @param QueryBuilder $queryBuilder
     * @param Request $request
     */
    public function apply(ResourceInterface $resource, QueryBuilder $queryBuilder, Request $request)
    {
        $fieldNames = $this->getDateFieldNames($resource);

        foreach ($this->extractProperties($request) as $property => $values) {
            $property = preg_replace('/' . preg_quote($this->assocName, '/') . '_/', '', $property);
            // Expect $values to be an array having the period as keys and the date value as values
            if (!isset($fieldNames[$property]) || !is_array($values) || !$this->isPropertyEnabled($property)) {
                continue;
            }

            $nullManagement = isset($this->properties[$property]) ? $this->properties[$property] : null;

            if (self::EXCLUDE_NULL === $nullManagement) {
                $queryBuilder->andWhere($queryBuilder->expr()->isNotNull(sprintf('o.%s', $property)));
            }

            if (isset($values[self::PARAMETER_BEFORE])) {
                $this->addWhere(
                    $queryBuilder,
                    $property,
                    self::PARAMETER_BEFORE,
                    $values[self::PARAMETER_BEFORE],
                    $nullManagement
                );
            }

            if (isset($values[self::PARAMETER_AFTER])) {
                $this->addWhere(
                    $queryBuilder,
                    $property,
                    self::PARAMETER_AFTER,
                    $values[self::PARAMETER_AFTER],
                    $nullManagement
                );
            }
        }
    }

    /**
     * Gets the description of this filter for the given resource.
     *
     * Returns an array with the filter parameter names as keys and array with the following data as values:
     *   - property: the property where the filter is applied
     *   - type: the type of the filter
     *   - required: if this filter is required
     *   - strategy: the used strategy
     * The description can contain additional data specific to a filter.
     *
     * @param ResourceInterface $resource
     *
     * @return array
     */
    public function getDescription(ResourceInterface $resource)
    {
        $description = [];

        $entityClass = $this->getClassMetadata($resource)->getAssociationTargetClass($this->assocName);
        $metadata = $this->managerRegistry->getManagerForClass($entityClass)->getClassMetadata($entityClass);
        foreach ($metadata->getFieldNames() as $fieldName) {
            // $fieldName = $this->assocName . '.' . $fieldName;
            if ($this->isPropertyEnabled($fieldName)) {
                $description += $this->getFilterDescription($this->assocName . '.' . $fieldName, self::PARAMETER_BEFORE);
                $description += $this->getFilterDescription($this->assocName . '.' . $fieldName, self::PARAMETER_AFTER);
            }
        }

        return $description;
    }

    /**
     * Gets filter description.
     *
     * @param string $fieldName
     * @param string $period
     *
     * @return array
     */
    private function getFilterDescription($fieldName, $period)
    {
        return [
            sprintf('%s[%s]', $fieldName, $period) => [
                'property' => $fieldName,
                'type' => '\DateTime',
                'required' => false,
            ],
        ];
    }

    /**
     * Gets names of fields with a date type.
     *
     * @param ResourceInterface $resource
     *
     * @return array
     */
    private function getDateFieldNames(ResourceInterface $resource)
    {
        $classMetadata = $this->getAssociationClassMetadata($resource);
        $dateFieldNames = [];

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if (isset(self::$doctrineDateTypes[$classMetadata->getTypeOfField($fieldName)])) {
                $dateFieldNames[$fieldName] = true;
            }
        }

        return $dateFieldNames;
    }

    private function getAssociationClassMetadata(ResourceInterface $resource) {
        $entityClass = $this->getClassMetadata($resource)->getAssociationTargetClass($this->assocName);
        return $this->managerRegistry->getManagerForClass($entityClass)->getClassMetadata($entityClass);
    }

    /**
     * Adds the where clause accordingly to the choosed null management.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $property
     * @param string       $parameter
     * @param string       $value
     * @param int|null     $nullManagement
     */
    private function addWhere(QueryBuilder $queryBuilder, $property, $parameter, $value, $nullManagement)
    {
        $queryParameter = sprintf('date_%s_%s', $parameter, str_replace('.', '_', $property));
        $queryBuilder->join('o' . '.' . $this->assocName, '__assoc');
        $where = sprintf('__assoc.%s %s= :%s', $property, self::PARAMETER_BEFORE === $parameter ? '<' : '>', $queryParameter);

        $queryBuilder->setParameter($queryParameter, new \DateTime($value));

        if (null === $nullManagement || self::EXCLUDE_NULL === $nullManagement) {
            $queryBuilder->andWhere($where);

            return;
        }

        throw new \Exception('@TODO: Not yet implemented');


        if (
            (self::PARAMETER_BEFORE === $parameter && self::INCLUDE_NULL_BEFORE === $nullManagement)
            ||
            (self::PARAMETER_AFTER === $parameter && self::INCLUDE_NULL_AFTER === $nullManagement)
        ) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                $where,
                $queryBuilder->expr()->isNull(sprintf('o.%s', $property))
            ));

            return;
        }

        $queryBuilder->andWhere($queryBuilder->expr()->andX(
            $where,
            $queryBuilder->expr()->isNotNull(sprintf('o.%s', $property))
        ));
    }
}