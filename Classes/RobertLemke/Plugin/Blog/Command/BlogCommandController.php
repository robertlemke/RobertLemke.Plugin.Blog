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
	 * @var \RobertLemke\Plugin\Blog\Domain\Repository\BlogRepository
	 */
	protected $blogRepository;

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
	 * Migrate to Neos-based blog
	 *
	 * This command migrates an existing blog based on the standalone blog app to
	 * a Neos blog based on nodes.
	 *
	 * @param string $blogNodePath Absolute node path of the parent node which will contain the blog.
	 * @param string $workspace Name of the workspace to create the blog posts in. By default, the "live" workspace is used.
	 * @param boolean $dryRun Don't execute the migration but print what would be done
	 * @return void
	 */
	public function migrateCommand($blogNodePath, $workspace = 'live', $dryRun = FALSE) {
		$blog = $this->blogRepository->findActive();
		if ($blog === NULL) {
			$this->outputLine('No active blog found.');
			$this->quit(1);
		}

		$pageContentType = $this->contentTypeManager->getContentType('TYPO3.Neos.ContentTypes:Page');
		$shortcutContentType = $this->contentTypeManager->getContentType('TYPO3.Neos.ContentTypes:Shortcut');
		$sectionContentType = $this->contentTypeManager->getContentType('TYPO3.Neos.ContentTypes:Section');
		$textContentType = $this->contentTypeManager->getContentType('TYPO3.Neos.ContentTypes:Text');
		$imageContentType = $this->contentTypeManager->getContentType('TYPO3.Neos.ContentTypes:Image');
		$postContentType = $this->contentTypeManager->getContentType('RobertLemke.Plugin.Blog:Post');
		$commentContentType = $this->contentTypeManager->getContentType('RobertLemke.Plugin.Blog:Comment');
		$postPluginContentType = $this->contentTypeManager->getContentType('RobertLemke.Plugin.Blog:PostPlugin');

		$contentContext = new ContentContext($workspace);
		$this->nodeRepository->setContext($contentContext);

		$blogBaseNode = $contentContext->getNode($blogNodePath);
		if ($blogBaseNode === NULL) {
			$this->outputLine('The blog base node "%s" does not exist - you need to create a page with that path manually.', array($blogNodePath));
			$this->quit(1);
		}

		foreach ($blogBaseNode->getChildNodes() as $childNode) {
			$childNode->remove();
		}

		$this->outputLine('Posts base node "%s" needs to be created.', array($blogNodePath . '/posts'));
		$postsBaseNode = $blogBaseNode->createNode('posts', $pageContentType);
		$postsBaseNode->setProperty('title', 'Posts');

		$mainSectionNode = $postsBaseNode->createNode('main', $sectionContentType);

		$postPluginNode = $mainSectionNode->createNode(uniqid('plugin'), $postPluginContentType);
		$postPluginNode->setProperty('package', 'RobertLemke.Plugin.Blog');
		$postPluginNode->setProperty('controller', 'Post');
		$postPluginNode->setProperty('action', 'index');

		foreach ($blog->getPosts() as $post) {
			$slug = preg_replace('/[^a-z0-9\\-]/', '', strtolower(str_replace(' ', '-', $post->getTitle())));
			$this->outputLine('Create post node "%s"', array($slug, $post->getTitle()));

			$date = $post->getDate();
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
			$postNode->setProperty('title', $post->getTitle());
			$postNode->setProperty('datePublished', $post->getDate());
			$postNode->setProperty('category', $post->getCategory()->getName());
			$postNode->setProperty('tags', implode(', ', $post->getTags()->toArray()));

			$contentSectionNode = $postNode->createNode('main', $sectionContentType);

			$textNode = $contentSectionNode->createNode(uniqid('text'), $textContentType);
			$textNode->setProperty('text', $post->getContent());

			$oldImage = $post->getImage();
			if ($oldImage !== NULL) {
				$newImage = new Image($oldImage->getOriginalResource());
				$newImage->setTitle($oldImage->getTitle());

				$processingInstructions = array(
					array(
						'command' => 'resize',
						'options' => array(
							'size' => array(
								'width' => 550,
								'height' => intval((550 / $newImage->getWidth()) * $newImage->getHeight()),
							),
						),
					),
				);
				$imageVariant = new ImageVariant($newImage, $processingInstructions);

				$imageNode = $contentSectionNode->createNode(uniqid('image'), $imageContentType);
				$imageNode->setProperty('image', $imageVariant);
			}

			$commentsSectionNode = $postNode->createNode('comments', $sectionContentType);
			foreach ($post->getComments() as $comment) {
				$commentNode = $commentsSectionNode->createNode(uniqid('comment'), $commentContentType);
				$commentNode->setProperty('text', $comment->getContent());
				$commentNode->setProperty('author', $comment->getAuthor());
				$commentNode->setProperty('emailAddress', $comment->getEmailAddress());
				$commentNode->setProperty('datePublished', $comment->getDate());
				$commentNode->setProperty('spam', $comment->isSpam());
				$this->outputLine('   Create comment node "%s"', array($commentNode->getPath()));
			}
		}

	}

}
?>