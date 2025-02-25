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
                    $stringToTruncate .= $propertyValue;
                }
            }
        }

        $jumpPosition = strpos($stringToTruncate, '<!-- read more -->');

        if ($jumpPosition !== false) {
            return $this->stripUnwantedTags(substr($stringToTruncate, 0, ($jumpPosition - 1)));
        }

        $jumpPosition = strpos($stringToTruncate, '</p>');
        if ($jumpPosition !== false && $jumpPosition < ($maximumLength + 100)) {
            return $this->stripUnwantedTags(substr($stringToTruncate, 0, $jumpPosition + 4));
        }

        if (strlen($stringToTruncate) > $maximumLength) {
            return substr($this->stripUnwantedTags($stringToTruncate), 0, $maximumLength + 1) . ' ...';
        }

        return $this->stripUnwantedTags($stringToTruncate);
    }

    /**
     * @param NodeInterface $node
     * @return string
     * @throws NodeException
     */
    public function renderContent(NodeInterface $node): string
    {
        $content = '';
        $childNodes = $this->getContentNodesFromMainCollection($node);

        /** @var NodeInterface $contentNode */
        foreach ($childNodes as $contentNode) {
            if ($contentNode->getNodeType()->isOfType('Neos.NodeTypes:TextWithImage')) {
                $propertyValue = $contentNode->getProperty('image');
                $attributes = [
                    'width="' . $propertyValue->getWidth() . '"',
                    'height="' . $propertyValue->getHeight() . '"',
                    'src="' . $this->resourceManager->getPublicPersistentResourceUri($propertyValue->getResource()) . '"'
                ];
                $content .= $contentNode->getProperty('text');
                $content .= '<img ' . implode(' ', $attributes) . '/>';
            } elseif ($contentNode->getNodeType()->isOfType('Neos.NodeTypes:Image')) {
                $propertyValue = $contentNode->getProperty('image');
                $attributes = [
                    'width="' . $propertyValue->getWidth() . '"',
                    'height="' . $propertyValue->getHeight() . '"',
                    'src="' . $this->resourceManager->getPublicPersistentResourceUri($propertyValue->getResource()) . '"'
                ];
                $content .= '<img ' . implode(' ', $attributes) . '/>';
            } else {
                foreach ($contentNode->getProperties() as $propertyValue) {
                    if (!is_object($propertyValue) || method_exists($propertyValue, '__toString')) {
                        $content .= $propertyValue;
                    }
                }
            }
        }

        return $this->stripUnwantedTags($content);
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
