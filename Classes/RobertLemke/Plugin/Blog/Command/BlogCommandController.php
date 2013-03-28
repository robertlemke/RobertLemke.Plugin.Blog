<?php
namespace RobertLemke\Plugin\Blog\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "RobertLemke.Plugin.Blog".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Domain\Service\ContentContext;

/**
 * BlogCommand command controller for the RobertLemke.Plugin.Blog package
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
	 * @var \TYPO3\TYPO3CR\Domain\Service\NodeTypeManager
	 */
	protected $nodeTypeManager;

	/**
	 * Migrates to flat hierarchy
	 *
	 * This command migrates to the flat node scheme
	 *
	 * @param string $workspace The workspace to perform the migration in
	 * @return void
	 */
	public function migrateCommand($workspace) {
		$context = new ContentContext($workspace);
		$this->nodeRepository->setContext($context);
		$blogNode = $context->getCurrentSiteNode()->getNode('en/blog');
		$postsNode = $blogNode->getNode('posts');

		$posts = array();
		$yearNodes = array();
		$counter = 0;
		foreach ($postsNode->getChildNodes() as $yearNode) {
			$yearNodes[] = $yearNode;
			foreach ($yearNode->getChildNodes() as $monthNode) {
				foreach ($monthNode->getChildNodes() as $dayNode) {
					foreach ($dayNode->getChildNodes() as $postNode) {
						$postNode->moveInto($postsNode);
						$this->outputLine($postNode->getProperty('title'));
						$counter ++;
					}
				}
			}
		}

		$postsNode->setProperty('title', 'Blog');
		$postsNode->moveAfter($blogNode);

		$blogNode->remove();

		foreach ($yearNodes as $yearNode) {
			$yearNode->remove();
		}

		$this->outputLine('Migrated %s blog posts.', array($counter));
	}

}

?>