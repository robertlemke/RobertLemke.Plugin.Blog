<?php
declare(strict_types=1);

namespace RobertLemke\Plugin\Blog\Service;

/*
 * This file is part of the RobertLemke.Plugin.Blog package.
 *
 * (c) Robert Lemke
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\NodeAggregate\NodeName;
use Neos\ContentRepository\Domain\NodeType\NodeTypeConstraintFactory;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodes;
use Neos\ContentRepository\Exception\NodeException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;

/**
 * A service which can render specific views of blog related content
 */
#[Flow\Scope("singleton")]
class ContentService
{
    #[Flow\Inject]
    protected ResourceManager $resourceManager;

    #[Flow\Inject]
    protected NodeTypeConstraintFactory $nodeTypeConstraintFactory;

    /**
     * Renders a teaser text with up to $maximumLength characters, with an outermost <p> and some more tags removed,
     * from the given Node (fetches the first Neos.NodeTypes:Text childNode as a base).
     *
     * If '<!-- read more -->' is found, the teaser will be the preceding content and $maximumLength is ignored.
     *
     * @return string
     */
    public function renderTeaser(NodeInterface $node, int $maximumLength = 500): string
    {
        $stringToTruncate = '';
        $contentNodes = $this->getContentNodesFromMainCollection($node);

        /** @var NodeInterface $contentNode */
        foreach ($contentNodes as $contentNode) {
            foreach ($contentNode->getProperties() as $propertyValue) {
                if (!is_object($propertyValue) || method_exists($propertyValue, '__toString')) {
                    $stringToTruncate .= $propertyValue . PHP_EOL;
                }
            }
        }

        $readMorePosition = strpos($stringToTruncate, '<!-- read more -->');
        if ($readMorePosition !== false) {
            return $this->stripUnwantedTags(substr($stringToTruncate, 0, $readMorePosition - 1));
        }

        // Find all paragraph end positions
        $validPositions = array_filter($this->getPTagPositions($stringToTruncate), function($pos) use ($maximumLength) {
            return $pos < $maximumLength;
        });

        // If we found a suitable paragraph break, use it
        $bestEndPosition = max($validPositions);
        if ($bestEndPosition > 0) {
            return $this->stripUnwantedTags(substr($stringToTruncate, 0, $bestEndPosition));
        }

        if (strlen($stringToTruncate) > $maximumLength) {
            return substr($this->stripUnwantedTags($stringToTruncate), 0, $maximumLength + 1) . ' ...';
        }

        return $this->stripUnwantedTags($stringToTruncate);
    }

    /**
     * Find all positions of '</p>' in the given HTML string.
     *
     * @param string $html
     * @return int[]
     */
    protected function getPTagPositions(string $html): array {
        $positions = [];
        $offset = 0;

        while (($pos = strpos($html, '<p>', $offset)) !== false) {
            $positions[] = $pos;
            $offset = $pos + 1;
        }

        return $positions;
    }

    /**
     * @param NodeInterface $node
     * @return TraversableNodes
     */
    protected function getContentNodesFromMainCollection(NodeInterface $node): TraversableNodes
    {
        $childNodeConstraint = $this->nodeTypeConstraintFactory->parseFilterString('Neos.Neos:Content');
        return $node
            ->findNamedChildNode(NodeName::fromString('main'))
            ->findChildNodes($childNodeConstraint);
    }

    /**
     * Removes a, span, strong, b, blockquote tags from $content.
     *
     * If the content starts with <p> and ends with </p> these tags are stripped as well.
     *
     * Non-breaking space entities are replaced by a single space character.
     *
     * @param string $content The original content
     * @return string The stripped content
     */
    protected function stripUnwantedTags(string $content): string
    {
        $content = trim($content);
        $content = preg_replace(
            [
                '/\\<a [^\\>]+\\>/',
                '/\<\\/a\\>/',
                '/\\<span[^\\>]+\\>/',
                '/\\<\\/span>]+\\>/',
                '/\\<h1[^\\>]+\\>/',
                '/\\<\\/h1>]+\\>/',
                '/\\<h2[^\\>]+\\>/',
                '/\\<\\/h2>]+\\>/',
                '/\\<h3[^\\>]+\\>/',
                '/\\<\\/h3>]+\\>/',
                '/\\<\\\\?(strong|b|blockquote)\\>/'
            ],
            '',
            $content
        );
        $content = str_replace('&nbsp;', ' ', $content);

        if (strpos($content, '<p>') === 0 && substr($content, -4, 4) === '</p>') {
            $content = substr($content, 3, -4);
        }

        return trim($content);
    }
}
