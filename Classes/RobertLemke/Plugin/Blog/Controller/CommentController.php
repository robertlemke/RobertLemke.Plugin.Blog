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

/**
 * Comments controller for the Blog package
 */
class CommentController extends \RobertLemke\Plugin\Blog\Controller\AbstractBaseController {

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
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post The post which will contain the new comment
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Comment $newComment A fresh Comment object which has not yet been added to the repository
	 * @return void
	 */
	public function createAction(\RobertLemke\Plugin\Blog\Domain\Model\Post $post, \RobertLemke\Plugin\Blog\Domain\Model\Comment $newComment) {
		$post->addComment($newComment);

		if ($this->akismetService->isCommentSpam('', $newComment->getContent(), 'comment', $newComment->getAuthor(), $newComment->getEmailAddress())) {
			$newComment->setSpam(TRUE);
		}

		$this->postRepository->update($post);
		$this->addFlashMessage('Your new comment was created.');
		$this->emitCommentCreated($newComment, $post);
		$this->redirect('show', 'Post', NULL, array('post' => $post));
	}

	/**
	 * Removes a comment
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Comment $comment
	 * @return void
	 */
	public function deleteAction(\RobertLemke\Plugin\Blog\Domain\Model\Post $post, \RobertLemke\Plugin\Blog\Domain\Model\Comment $comment) {
		$post->removeComment($comment);
		$this->postRepository->update($post);
		$this->redirect('show', 'Post', NULL, array('post' => $post));
	}

	/**
	 * Marks a comment as spam
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Comment $comment
	 * @return void
	 */
	public function markSpamAction(\RobertLemke\Plugin\Blog\Domain\Model\Post $post, \RobertLemke\Plugin\Blog\Domain\Model\Comment $comment) {
		$this->akismetService->submitSpam('', $comment->getContent(), 'comment', $comment->getAuthor(), $comment->getEmailAddress());
		$comment->setSpam(TRUE);
		$this->postRepository->updateComment($post, $comment);
		$this->addFlashMessage('Marked comment as spam.');
		$this->redirect('show', 'Post', NULL, array('post' => $post));
	}

	/**
	 * Marks a comment as ham
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Comment $comment
	 * @return void
	 */
	public function markHamAction(\RobertLemke\Plugin\Blog\Domain\Model\Post $post, \RobertLemke\Plugin\Blog\Domain\Model\Comment $comment) {
		$this->akismetService->submitHam('', $comment->getContent(), 'comment', $comment->getAuthor(), $comment->getEmailAddress());
		$comment->setSpam(FALSE);
		$this->postRepository->updateComment($post, $comment);
		$this->addFlashMessage('Marked comment as ham.');
		$this->redirect('show', 'Post', NULL, array('post' => $post));
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
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Comment $comment
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post
	 * @return void
	 * @Flow\Signal
	 */
	protected function emitCommentCreated(\RobertLemke\Plugin\Blog\Domain\Model\Comment $comment, \RobertLemke\Plugin\Blog\Domain\Model\Post $post) {}
}

?>