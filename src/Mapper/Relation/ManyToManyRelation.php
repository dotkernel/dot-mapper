<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-ems
 * @author: n3vrax
 * Date: 12/12/2016
 * Time: 6:57 PM
 */

declare(strict_types=1);

namespace Dot\Ems\Mapper\Relation;

use Dot\Ems\Exception\InvalidArgumentException;
use Dot\Ems\Mapper\MapperInterface;

/**
 * Class ManyToManyRelation
 * @package Dot\Ems\Mapper\Relation
 */
class ManyToManyRelation extends OneToManyRelation
{
    protected $refType = RelationInterface::MANY_TO_MANY;

    /** @var  MapperInterface */
    protected $targetMapper;

    /** @var  string */
    protected $targetRefName;

    /** @var  bool */
    protected $createTargetRefs = true;

    /**
     * ManyToManyRelation constructor.
     * @param MapperInterface $mapper
     * @param null $refName
     * @param MapperInterface $targetMapper
     * @param $targetRefName
     * @param $fieldName
     */
    public function __construct(
        MapperInterface $mapper,
        $refName,
        MapperInterface $targetMapper,
        $targetRefName,
        $fieldName
    ) {
        parent::__construct($mapper, $refName, $fieldName);
        $this->targetMapper = $targetMapper;
        $this->targetRefName = $targetRefName;
    }

    /**
     * @param $refValue
     * @return array
     */
    public function fetchRef($refValue)
    {
        $linkEntities = parent::fetchRef($refValue);
        if ($linkEntities) {
            $refs = [];
            foreach ($linkEntities as $linkEntity) {
                $targetRefValue = $this->getProperty($linkEntity, $this->targetRefName);
                $ref = $this->targetMapper->fetch([$this->targetMapper->getIdentifierName() => $targetRefValue]);
                if ($ref) {
                    $refs[] = $ref;
                }
            }

            return $refs;
        }

        return null;
    }

    public function saveRef($refs, $refValue)
    {
        if (!$this->changeRefs) {
            return 0;
        }

        $affectedRows = 0;
        if (is_array($refs)) {
            //we delete and create the intersection entries from scratch
            $this->deleteRef($refValue);

            foreach ($refs as $ref) {
                if (!is_object($ref)) {
                    throw new InvalidArgumentException('References to delete contains invalid entities');
                }

                $id = $this->getProperty($ref, $this->targetMapper->getIdentifierName());
                if (!$id && $this->createTargetRefs) {
                    $this->targetMapper->create($ref);
                    $id = $this->getProperty($ref, $this->targetMapper->getIdentifierName());
                }

                if ($id) {
                    $intersectionEntity = $this->getMapper()->getPrototype();
                    $this->setProperty($intersectionEntity, $this->getRefName(), $refValue);
                    $this->setProperty($intersectionEntity, $this->targetRefName, $id);

                    $affectedRows += $this->getMapper()->create($intersectionEntity);
                }
            }
        } else {
            throw new InvalidArgumentException('Invalid parameter refs to save');
        }

        return $affectedRows;
    }

    /**
     * It deletes only the intersection table entries, the target entities should be managed in its own mapper-service
     * @param $refs
     * @param null $refValue
     * @return int|mixed
     * @throws \Exception
     */
    public function deleteRef($refs, $refValue = null)
    {
        if (!$this->deleteRefs) {
            return 0;
        }

        $affectedRows = 0;
        if (is_scalar($refs)) {
            $affectedRows = $this->getMapper()->delete([$this->getRefName() => $refs]);
        } elseif (is_array($refs) && $refValue !== null) {
            foreach ($refs as $ref) {
                if (!is_object($ref)) {
                    throw new InvalidArgumentException('References to delete contains invalid entities');
                }

                $id = $this->getProperty($ref, $this->targetMapper->getIdentifierName());
                if ($id) {
                    $affectedRows += $this->getMapper()->delete([
                        $this->getRefName() => $refValue,
                        $this->targetRefName => $id
                    ]);
                }
            }
        } else {
            throw new InvalidArgumentException('Invalid parameter refs to delete');
        }

        return $affectedRows;
    }

    /**
     * @return boolean
     */
    public function isCreateTargetRefs()
    {
        return $this->createTargetRefs;
    }

    /**
     * @param boolean $createTargetRefs
     * @return ManyToManyRelation
     */
    public function setCreateTargetRefs($createTargetRefs)
    {
        $this->createTargetRefs = $createTargetRefs;
        return $this;
    }
}
