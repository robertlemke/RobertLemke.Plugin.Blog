<?php
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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\ContentRepository\Domain\Model\NodeInterface;

/**
 * A service which can render specific views of blog related content
 *
 * @Flow\Scope("singleton")
 */
class ContentService
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * Renders a teaser text with up to $maximumLength characters, with an outermost <p> and some more tags removed,
     * from the given Node (fetches the first Neos.NodeTypes:Text childNode as a base).
     *
     * If '<!-- read more -->' is found, the teaser will be the preceding content and $maximumLength is ignored.
     *
     * @param NodeInterface $node
     * @param integer $maximumLength
     * @return mixed
     */
    public function renderTeaser(NodeInterface $node, $maximumLength = 500)
    {
        $stringToTruncate = '';

        /** @var NodeInterface $contentNode */
        foreach ($node->getNode('main')->getChildNodes('Neos.NodeTypes:Text') as $contentNode) {
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
        } else {
            return $this->stripUnwantedTags($stringToTruncate);
        }
    }

    /**
     * @param NodeInterface $node
     * @return string
     */
    public function renderContent(NodeInterface $node)
    {
        $content = '';

        /** @var NodeInterface $contentNode */
        foreach ($node->getNode('main')->getChildNodes('Neos.Neos:Content') as $contentNode) {
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
     * Removes a, span, strong, b, blockquote tags from $content.
     *
     * If the content starts with <p> and ends with </p> these tags are stripped as well.
     *
     * Non-breaking space entities are replaced by a single space character.
     *
     * @param string $content The original content
     * @return string The stripped content
     */
    protected function stripUnwantedTags($content)
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

        if (substr($content, 0, 3) === '<p>' && substr($content, -4, 4) === '</p>') {
            $content = substr($content, 3, -4);
        }

        return trim($content);
    }
}
