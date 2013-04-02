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

use RobertLemke\Rss\Channel;
use RobertLemke\Rss\Feed;
use RobertLemke\Rss\Item;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Mvc\Routing\UriBuilder;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\TYPO3CR\Domain\Model\PersistentNodeInterface;

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
	 * @Flow\Inject
	 * @var \TYPO3\Flow\I18n\Service
	 */
	protected $i18nService;

	/**
	 * @Flow\Inject
	 * @var \RobertLemke\Plugin\Blog\Service\ContentService
	 */
	protected $contentService;

	/**
	 * Displays a list of most recent blog posts
	 *
	 * @return void
	 */
	public function indexAction() {
		$currentNode = $this->nodeRepository->getContext()->getCurrentNode();
		$this->view->assign('postsNode', $currentNode);
	}

	/**
	 * Renders an RSS feed
	 *
	 * @return string
	 */
	public function rssAction() {
		$uriBuilder = new UriBuilder();
		$uriBuilder->setRequest($this->request->getMainRequest());
		$uriBuilder->setLinkProtectionEnabled(FALSE);
		$uriBuilder->setCreateAbsoluteUri(TRUE);
		$uriBuilder->setFormat('xml');

		if ($this->settings['feed']['uri'] !== '') {
			$feedUri = $this->settings['feed']['uri'];
		} else {
			$feedUri = $uriBuilder->uriFor('show', array('node' => $this->nodeRepository->getContext()->getCurrentNode()), 'Frontend\Node', 'TYPO3.Neos');
		}

		$channel = new Channel();
		$channel->setTitle($this->settings['feed']['title']);
		$channel->setDescription($this->settings['feed']['description']);
		$channel->setFeedUri($feedUri);
		$channel->setWebsiteUri($this->request->getHttpRequest()->getBaseUri());
		$channel->setLanguage((string)$this->i18nService->getConfiguration()->getCurrentLocale());

		$postsNode = $this->nodeRepository->getContext()->getCurrentNode()->getParent();

		foreach ($postsNode->getChildNodes('RobertLemke.Plugin.Blog:Post') as $postNode) {
			/* @var $postNode PersistentNodeInterface */

			$postUri = $uriBuilder->uriFor('show', array('node' => $postNode), 'Frontend\Node', 'TYPO3.Neos');

			$item = new Item();
			$item->setTitle($postNode->getProperty('title'));
			$item->setGuid($postNode->getIdentifier());

				// TODO: Remove this once all old node properties are migrated:
			$publicationDate = $postNode->getProperty('datePublished');
			if (is_string($publicationDate)) {
				$publicationDate = \DateTime::createFromFormat('Y-m-d', $publicationDate);
				$postNode->setProperty('datePublished', $publicationDate);
			}

			$item->setPublicationDate($postNode->getProperty('datePublished'));
			$item->setItemLink((string)$postUri);
			$item->setCommentsLink((string)$postUri . '#comments');

				// TODO: Remove this once all old node properties are migrated:
			$author = $postNode->getProperty('author');
			if ($author === NULL) {
				$author = 'Robert Lemke';
				$postNode->setProperty('author', $author);
			}
			$item->setCreator($author);

#			$item->setCategories(array('test'));
			$description = $this->contentService->renderTeaser($postNode) . ' <a href="' . $postUri . '">Read more</a>';
			$item->setDescription($description);
			$channel->addItem($item);
		}

			// This won't work yet (plugin sub responses can't set headers yet) but keep that as a reminder:
		$headers = $this->response->getHeaders();
		$headers->setCacheControlDirective('s-max-age', 3600);
		$headers->set('Content-Type', 'application/rss+xml');

		$feed = new Feed();
		$feed->addChannel($channel);
		return $feed->render();
	}

	/**
	 * Creates a new blog post node
	 *
	 * @return void
	 */
	public function createAction() {
		$contentContext = $this->nodeRepository->getContext();
		$parentNode = $contentContext->getCurrentNode();

		$nodeTemplate = new NodeTemplate();
		$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('RobertLemke.Plugin.Blog:Post'));
		$nodeTemplate->setProperty('title', 'A new blog post');
		$nodeTemplate->setProperty('datePublished', $contentContext->getCurrentDateTime());

		$slug = uniqid('post');
		$postNode = $parentNode->createNodeFromTemplate($nodeTemplate, $slug);

		$currentlyFirstPostNode = $parentNode->getPrimaryChildNode();
		if ($currentlyFirstPostNode !== NULL) {
			$postNode->moveBefore($currentlyFirstPostNode);
		}

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