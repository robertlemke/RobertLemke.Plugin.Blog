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

use RobertLemke\Plugin\Blog\Domain\Model\Comment;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Fluid\Core\Widget\AbstractWidgetController;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * Comments controller for the Blog package
 */
class CommentController extends ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Service\ContentTypeManager
	 */
	protected $contentTypeManager;

	/**
	 * @Flow\Inject
	 * @var \RobertLemke\Akismet\Service
	 */
	protected $akismetService;

	/**
	 * Initialize the Akismet service
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->akismetService->setCurrentRequest($this->request->getHttpRequest());
	}

	/**
	 * Creates a new comment
	 *
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $postNode The post node which will contain the new comment
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Comment $newComment A fresh Comment object
	 * @return void
	 */
	public function createAction(NodeInterface $postNode, Comment $newComment) {
		$commentContentType = $this->contentTypeManager->getContentType('RobertLemke.Plugin.Blog:Comment');
		$commentNode = $postNode->getNode('comments')->createNode(uniqid('comment'), $commentContentType);
		$commentNode->setProperty('text', filter_var($newComment->getContent(), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
		$commentNode->setProperty('author', $newComment->getAuthor());
		$commentNode->setProperty('emailAddress', $newComment->getEmailAddress());
		$commentNode->setProperty('datePublished', new \DateTime());
		$commentNode->setProperty('spam', FALSE);

		if ($this->akismetService->isCommentSpam('', $newComment->getContent(), 'comment', $newComment->getAuthor(), $newComment->getEmailAddress())) {
			$commentNode->setProperty('spam', TRUE);
		}

		$this->addFlashMessage('Your new comment was created.');
		$this->emitCommentCreated($commentNode, $postNode);
		$this->redirect('show', 'Frontend\Node', 'TYPO3.Neos', array('node' => $postNode));
	}

	/**
	 * A special action which is called if the originally intended action could
	 * not be called, for example if the arguments were not valid.
	 *
	 * @return string
	 * @api
	 */
	protected function errorAction() {
		$errorFlashMessage = $this->getErrorFlashMessage();
		if ($errorFlashMessage !== FALSE) {
			$this->flashMessageContainer->addMessage($errorFlashMessage);
		}
		$postNode = $this->arguments['postNode']->getValue();
		if ($postNode !== NULL) {
			$this->redirect('show', 'Frontend\Node', 'TYPO3.Neos', array('node' => $postNode));
		}

		$message = 'An error occurred while trying to call ' . get_class($this) . '->' . $this->actionMethodName . '().' . PHP_EOL;
		foreach ($this->arguments->getValidationResults()->getFlattenedErrors() as $propertyPath => $errors) {
			foreach ($errors as $error) {
				$message .= 'Error for ' . $propertyPath . ':  ' . $error->render() . PHP_EOL;
			}
		}

		return $message;
	}

	/**
	 * Override getErrorFlashMessage to present nice flash error messages.
	 *
	 * @return \TYPO3\Flow\Error\Message
	 */
	protected function getErrorFlashMessage() {
		switch ($this->actionMethodName) {
			case 'createAction' :
				return new \TYPO3\Flow\Error\Error('Could not create the new comment');
			default :
				return parent::getErrorFlashMessage();
		}
	}

	/**
	 * Signal which informs about a newly created comment
	 *
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $commentNode The comment node
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $postNode The post node
	 * @return void
	 * @Flow\Signal
	 */
	protected function emitCommentCreated(NodeInterface $commentNode, NodeInterface $postNode) {}
}

?>