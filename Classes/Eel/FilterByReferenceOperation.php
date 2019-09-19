<?php
declare(strict_types=1);

namespace RobertLemke\Plugin\Blog\Eel;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\FlowQueryException;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;

/**
 * FlowQuery operation to filter by properties of type reference or references
 */
class FilterByReferenceOperation extends AbstractOperation
{
    /**
     * {@inheritdoc}
     */
    protected static $shortName = 'filterByReference';

    /**
     * {@inheritdoc}
     */
    protected static $priority = 100;

    /**
     * We can only handle CR Nodes.
     *
     * @param $context
     * @return bool
     */
    public function canEvaluate($context)
    {
        return (!isset($context[0]) || ($context[0] instanceof NodeInterface));
    }

    /**
     * First argument is property to filter by, must be of reference of references type.
     * Second is object to filter by, must be Node.
     *
     * @param FlowQuery $flowQuery
     * @param array $arguments The arguments for this operation.
     * @return void
     * @throws FlowQueryException
     * @throws \Neos\ContentRepository\Exception\NodeException
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments)
    {
        if (empty($arguments[0])) {
            throw new FlowQueryException('filterByReference() needs reference property name by which nodes should be filtered', 1545778273);
        }
        if (empty($arguments[1])) {
            throw new FlowQueryException('filterByReference() needs node reference by which nodes should be filtered', 1545778276);
        }

        /** @var NodeInterface $nodeReference */
        [$filterByPropertyPath, $nodeReference] = $arguments;

        $filteredNodes = [];
        foreach ($flowQuery->getContext() as $node) {
            /** @var NodeInterface $node */
            $propertyValue = $node->getProperty($filterByPropertyPath);
            if ($nodeReference === $propertyValue || (is_array($propertyValue) && in_array($nodeReference, $propertyValue, true))) {
                $filteredNodes[] = $node;
            }
        }

        $flowQuery->setContext($filteredNodes);
    }
}
