<?php
namespace RobertLemke\Plugin\Blog\Controller;

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
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Mvc\Routing\UriBuilder;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;

/**
 * The posts controller for the Blog package
 *
 * @Flow\Scope("singleton")
 */
class PostController extends ActionController {

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
	 * Displays a list of most recent blog posts
	 *
	 * @return void
	 */
	public function indexAction() {
		$currentNode = $this->nodeRepository->getContext()->getCurrentNode();
		$posts = array();
		$counter = 0;
		foreach ($currentNode->getChildNodes() as $yearNode) {
			foreach ($yearNode->getChildNodes() as $monthNode) {
				foreach ($monthNode->getChildNodes() as $dayNode) {
					foreach ($dayNode->getChildNodes() as $postNode) {
						$posts[] = $postNode;
						$counter ++;
						if ($counter > 15) {
							break 4;
						}
					}
				}
			}
		}
		$this->view->assign('posts', $posts);
	}

	/**
	 * Creates a new blog post node
	 *
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeTemplate<RobertLemke.Plugin.Blog:Post> $nodeTemplate
	 * @return void
	 */
	public function createAction(NodeTemplate $nodeTemplate) {
		$shortcutContentType = $this->nodeTypeManager->getNodeType('TYPO3.Neos.NodeTypes:Shortcut');
		$parentNode = $this->nodeRepository->getContext()->getCurrentNode();

		$slug = uniqid('post');
		$date = new \DateTime();

		$yearNode = $parentNode->getNode($date->format('Y'));
		if ($yearNode === NULL) {
			$yearNode = $parentNode->createNode($date->format('Y'), $shortcutContentType);
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

		$postNode = $dayNode->createNodeFromTemplate($nodeTemplate, $slug);
		$postNode->setProperty('datePublished', $date);
		$postNode->setProperty('category', '');
		$postNode->setProperty('tags', '');

		# $this->redirect('show', 'Frontend\Node', 'TYPO3.Neos', array('node' => $postNode));
		#
		# The above redirect won't work as it is using the nested action request instead
		# of the main request. That may be an unwanted behavior. For now use this
		# workaround:

		$mainRequest = $this->request->getMainRequest();
		$mainUriBuilder = new UriBuilder();
		$mainUriBuilder->setRequest($mainRequest);
		$uri = $mainUriBuilder
			->reset()
			->setCreateAbsoluteUri(TRUE)
			->uriFor('show', array('node' => $postNode));
		$this->redirectToUri($uri);
	}

}
?>