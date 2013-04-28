<?php
namespace RobertLemke\Plugin\Blog\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "RobertLemke.Plugin.Blog"*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Domain\Service\ContentContext;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;

/**
 * BlogCommand command controller for the RobertLemke.Plugin.Blog package
 *
 * @Flow\Scope("singleton")
 */
class AtomImportCommandController extends \TYPO3\Flow\Cli\CommandController {

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
	 * Imports atom data into the blog
	 *
	 * @param string $workspace The workspace to work in
	 * @param string $atomUrl The atom feed to import
	 * @param string $atomFile The atom file to import
	 * @return void
	 */
	public function migrateCommand($workspace, $atomUrl = NULL, $atomFile = NULL) {
		if (!class_exists('SimplePie')) {
			$this->outputLine('The Atom import needs simplepie/simplepie, which you can install using composer.');
			$this->quit(1);
		}
		$parser = new \SimplePie();
		$parser->enable_order_by_date();
		$parser->enable_cache(FALSE);

		if ($atomUrl === NULL && $atomFile === NULL) {
			$this->outputLine('Well, I thought I was supposed to import something? Then pass --atom-url or --atom-file, please.');
			$this->quit(1);
		}

		if ($atomFile !== NULL) {
			$parser->set_raw_data(file_get_contents($atomFile));
		}
		if ($atomUrl !== NULL) {
			$parser->set_feed_url($atomUrl);
		}
		$parser->strip_attributes();
		$parser->strip_htmltags(array_merge($parser->strip_htmltags, array('span')));
		$parser->init();
		$items = $parser->get_items();

		$comments = array();
		/** @var $item \SimplePie_Item */
		/** @var $postComment \SimplePie_Item */
		/** @var $category \SimplePie_Category */
		foreach ($items as $item) {
			$categories = $item->get_categories();
			foreach ($categories as $category) {
				if ($category->get_term() === 'http://schemas.google.com/blogger/2008/kind#comment') {
					$t = current($item->get_item_tags('http://purl.org/syndication/thread/1.0', 'in-reply-to')[0]['attribs']);
					$comments[$t['ref']][$item->get_date('U')] = $item;
				}
			}
		}

		$context = new ContentContext($workspace);
		$this->nodeRepository->setContext($context);
		$blogNode = $context->getCurrentSiteNode()->getNode('blog');
		$textNodeType = $this->nodeTypeManager->getNodeType('TYPO3.Neos.NodeTypes:Text');
		$commentNodeType = $this->nodeTypeManager->getNodeType('RobertLemke.Plugin.Blog:Comment');
		$counter = 0;
		foreach ($parser->get_items() as $item) {

			$tags = array();
			$itemIsPost = FALSE;
			foreach ($item->get_categories() as $category) {
				if ($category->get_term() === 'http://schemas.google.com/blogger/2008/kind#post') {
					$itemIsPost = TRUE;
				}
				if ($category->get_scheme() === 'http://www.blogger.com/atom/ns#') {
					$tags[] = $category->get_term();
				}
			}
			if (!$itemIsPost) {
				continue;
			}

			$nodeTemplate = new NodeTemplate();
			$nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('RobertLemke.Plugin.Blog:Post'));
			$nodeTemplate->setProperty('title', $item->get_title());
			$nodeTemplate->setProperty('author', 'Karsten Dambekalns');
			$published = new \DateTime();
			$published->setTimestamp($item->get_date('U'));
			$nodeTemplate->setProperty('datePublished', $published);
			$nodeTemplate->setProperty('tags', implode(',', $tags));

			$slug = strtolower(str_replace(array(' ', ',', ':', 'ü', 'à', 'é', '?', '!', '[', ']', '.', '\''), array('-', '', '', 'u', 'a', 'e', '', '', '', '', '-', ''), $item->get_title()));
			$postNode = $blogNode->createNodeFromTemplate($nodeTemplate, $slug);
			$postNode->getNode('main')->createNode(uniqid('node'), $textNodeType)->setProperty('text', $item->get_content());

			$postComments = isset($comments[$item->get_id()]) ? $comments[$item->get_id()] : array();
			if ($postComments !== array()) {
				$commentsNode = $postNode->getNode('comments');
				foreach ($postComments as $postComment) {
					$commentNode = $commentsNode->createNode(uniqid('comment-'), $commentNodeType);
					$commentNode->setProperty('author', html_entity_decode($postComment->get_author()->get_name(), ENT_QUOTES, 'utf-8'));
					$commentNode->setProperty('emailAddress', $postComment->get_author()->get_email());
					$commentNode->setProperty('uri', $postComment->get_author()->get_link());
					$commentNode->setProperty('datePublished', new \DateTime($postComment->get_date()));
					$commentText = preg_replace('/<br[ \/]*>/i', chr(10), $postComment->get_content());
					$commentText = html_entity_decode($commentText, ENT_QUOTES, 'utf-8');
					$commentNode->setProperty('text', $commentText);
					$commentNode->setProperty('spam', FALSE);
					$previousCommentNode = $commentNode;
					if ($previousCommentNode !== NULL) {
						$commentNode->moveAfter($previousCommentNode);
					}
				}
			}

			$counter++;
			$this->outputLine($nodeTemplate->getProperty('title'));
		}

		$this->outputLine('Imported %s blog posts.', array($counter));
	}

}

?>