<?php
namespace RobertLemke\Plugin\Blog\Command;

/*                                                                        *
 * This script belongs to the FLOW3 package "Blog".                       *
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
use TYPO3\Neos\Domain\Service\ContentContext;
use TYPO3\Media\Domain\Model\Image;
use TYPO3\Media\Domain\Model\ImageVariant;

/**
 * Command controller providing various maintenance commands for the blog plugin.
 *
 * @Flow\Scope("singleton")
 */
class BlogCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\NodeRepository
	 */
	protected $nodeRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Service\ContentTypeManager
	 */
	protected $contentTypeManager;

	/**
	 * Create a new blog post
	 *
	 * This command creates a new and empty blog post in the specified workspace.
	 *
	 * @param string $blogNodePath Absolute node path of the parent node which contains the blog.
	 * @param string $workspace Name of the workspace to create the blog posts in.
	 * @return void
	 */
	public function createPostCommand($blogNodePath, $workspace) {
		$shortcutContentType = $this->contentTypeManager->getContentType('TYPO3.Neos.ContentTypes:Shortcut');
		$postContentType = $this->contentTypeManager->getContentType('RobertLemke.Plugin.Blog:Post');

		$contentContext = new ContentContext($workspace);
		$this->nodeRepository->setContext($contentContext);

		$blogBaseNode = $contentContext->getNode($blogNodePath);
		if ($blogBaseNode === NULL) {
			$this->outputLine('The blog base node "%s" does not exist - you need to create a page with that path manually.', array($blogNodePath));
			$this->quit(1);
		}

		$postsBaseNode = $blogBaseNode->getNode('posts');

		$slug = uniqid('post');
		$date = new \DateTime();

		$yearNode = $postsBaseNode->getNode($date->format('Y'));
		if ($yearNode === NULL) {
			$yearNode = $postsBaseNode->createNode($date->format('Y'), $shortcutContentType);
			$yearNode->setProperty('title', $date->format('Y'));
		}

		$monthNode = $yearNode->getNode($date->format('m'));
		if ($monthNode === NULL) {
			$monthNode = $yearNode->createNode($date->format('m'), $shortcutContentType);
			$monthNode->setProperty('title', $date->format('m'));
		}

		$dayNode = $monthNode->getNode($date->format('d'));
		if ($dayNode === NULL) {
			$dayNode = $monthNode->createNode($date->format('d'), $shortcutContentType);
			$dayNode->setProperty('title', $date->format('d'));
		}

		$postNode = $dayNode->createNode($slug, $postContentType);
		$postNode->setProperty('title', 'New blog post');
		$postNode->setProperty('datePublished', $date);
		$postNode->setProperty('category', '');
		$postNode->setProperty('tags', '');

		$this->outputLine('Created new blog post at %s.', array($postNode->getPath()));
	}

}
?>