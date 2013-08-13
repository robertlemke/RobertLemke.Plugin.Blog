<?php
namespace RobertLemke\Plugin\Blog\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "Blog".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * A service which can render specific views of blog related content
 *
 * @Flow\Scope("singleton")
 */
class ContentService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * Renders the given Node as a teaser text with up to 600 characters, with all <p> and <a> tags removed.
	 *
	 * @param NodeInterface $node
	 * @return mixed
	 */
	public function renderTeaser(NodeInterface $node) {
		$stringToTruncate = '';

		foreach ($node->getNode('main')->getChildNodes('TYPO3.Neos.NodeTypes:Text') as $contentNode) {
			foreach ($contentNode->getProperties() as $propertyValue) {
				if (!is_object($propertyValue) || method_exists($propertyValue, '__toString')) {
					$stringToTruncate .= $propertyValue;
				}
			}
		}

		$jumpPosition = strpos($stringToTruncate, '<!-- read more -->');

		if ($jumpPosition !== FALSE) {
			return $this->stripUnwantedTags(substr($stringToTruncate, 0, ($jumpPosition - 1)));
		}

		$jumpPosition = strpos($stringToTruncate, '</p>');
		if ($jumpPosition !== FALSE && $jumpPosition < 600) {
			return $this->stripUnwantedTags(substr($stringToTruncate, 0, $jumpPosition + 4));
		}

		if (strlen($stringToTruncate) > 500) {
			return substr($this->stripUnwantedTags($stringToTruncate), 0, 501) . ' ...';
		} else {
			return $this->stripUnwantedTags($stringToTruncate);
		}

	}

	/**
	 * @param NodeInterface $node
	 * @return mixed
	 */
	public function renderContent(NodeInterface $node) {
		$content = '';

		/** @var \TYPO3\TYPO3CR\Domain\Model\Node $contentNode */
		foreach ($node->getNode('main')->getChildNodes('TYPO3.Neos:Content') as $contentNode) {
			if ($contentNode->getNodeType()->isOfType('TYPO3.Neos.NodeTypes:TextWithImage')) {
					$propertyValue = $contentNode->getProperty('image');
					$attributes = array(
						'width="' . $propertyValue->getWidth() . '"',
						'height="' . $propertyValue->getHeight() . '"',
						'src="' . $this->resourcePublisher->getPersistentResourceWebUri($propertyValue->getResource()) . '"',
					);
					$content .= $contentNode->getProperty('text');
					$content .= '<img ' . implode(' ', $attributes) . '/>';
			} elseif ($contentNode->getNodeType()->isOfType('TYPO3.Neos.NodeTypes:Image')) {
				$propertyValue = $contentNode->getProperty('image');
				$attributes = array(
					'width="' . $propertyValue->getWidth() . '"',
					'height="' . $propertyValue->getHeight() . '"',
					'src="' . $this->resourcePublisher->getPersistentResourceWebUri($propertyValue->getResource()) . '"',
				);
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
	 * If the content starts with <p> and ends with </p> these tags are stripped.
	 *
	 * @param string $content The original content
	 * @return string The stripped content
	 */
	protected function stripUnwantedTags($content) {
		$content = trim($content);
		$content = preg_replace(array('/\\<a [^\\>]+\\>/', '/\<\\/a\\>/', '/\\<span style[^\\>]+\\>/'), '', $content);
		$content = str_replace('&nbsp;', ' ', $content);

		if (substr($content, 0, 3) === '<p>' && substr($content, -4, 4) === '</p>') {
			$content = substr($content, 3, -4);
		}
		return trim($content);
	}
}

?>