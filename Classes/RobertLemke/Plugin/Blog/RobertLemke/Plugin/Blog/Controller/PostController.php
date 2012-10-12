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
 * The posts controller for the Blog package
 *
 * @Flow\Scope("singleton")
 */
class PostController extends \RobertLemke\Plugin\Blog\Controller\AbstractBaseController {

	/**
	 * @Flow\Inject
	 * @var \RobertLemke\Plugin\Blog\Domain\Repository\CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * @Flow\Inject
	 * @var \RobertLemke\Akismet\Service
	 */
	protected $akismetService;

	/**
	 * List action for this controller. Displays latest posts
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Tag $tag The tag to display posts for
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Category $category The category to display posts for
	 * @return void
	 */
	public function indexAction(\RobertLemke\Plugin\Blog\Domain\Model\Tag $tag = NULL, \RobertLemke\Plugin\Blog\Domain\Model\Category $category = NULL) {
		if ($tag === NULL && $category === NULL) {
			$posts = $this->postRepository->findByBlog($this->blog);
		} elseif ($tag !== NULL) {
			$posts = $this->postRepository->findByTagAndBlog($tag, $this->blog);
			$this->view->assign('tag', $tag);
		} else {
			$posts = $this->postRepository->findByCategoryAndBlog($category, $this->blog);
			$this->view->assign('category', $category);
		}
		$this->view->assign('blog', $this->blog);
		$this->view->assign('posts', $posts);
		$this->view->assign('recentPosts', $this->postRepository->findRecentByBlog($this->blog));
	}

	/**
	 * Action that displays one single post
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post The post to display
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Comment $newComment If the comment form as has been submitted but the comment was not valid, this argument is used for displaying the entered values again
	 * @Flow\IgnoreValidation("$post")
	 * @Flow\IgnoreValidation("$newComment")
	 * @return void
	 */
	public function showAction(\RobertLemke\Plugin\Blog\Domain\Model\Post $post, \RobertLemke\Plugin\Blog\Domain\Model\Comment $newComment = NULL) {
		$this->view->assign('post', $post);
		$this->view->assign('blog', $post->getBlog());
		$this->view->assign('previousPost', $this->postRepository->findPrevious($post));
		$this->view->assign('nextPost', $this->postRepository->findNext($post));
		$this->view->assign('recentPosts', $this->postRepository->findRecentByBlog($post->getBlog()));
		$this->view->assign('newComment', $newComment);
	}

	/**
	 * Displays a form for creating a new post
	 *
	 * @return void
	 */
	public function newAction() {
		$account = $this->findCurrentAccount();
		$newPost = new \RobertLemke\Plugin\Blog\Domain\Model\Post();
		$newPost->setAuthor($account->getParty()->getName()->getFullName());

		$this->view->assign('blog', $this->blog);
		$this->view->assign('existingPosts', $this->postRepository->findByBlog($this->blog));
		$this->view->assign('categories', $this->categoryRepository->findAll());
		$this->view->assign('newPost', $newPost);
	}

	/**
	 * Set property mapper configuration for post creation
	 *
	 * @return void
	 */
	public function initializeCreateAction() {
		$this->arguments['newPost']->getPropertyMappingConfiguration()->allowCreationForSubProperty('image');
	}

	/**
	 * Creates a new post
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $newPost A fresh Post object which has not yet been added to the repository
	 * @return void
	 */
	public function createAction(\RobertLemke\Plugin\Blog\Domain\Model\Post $newPost) {
		$this->postRepository->add($newPost);
		$this->blog->addPost($newPost);
		$this->blogRepository->update($this->blog);
		$this->addFlashMessage('Your new post was created.');
		$this->redirect('index');
	}

	/**
	 * Displays a form for editing an existing post
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post An existing post object taken as a basis for the rendering
	 * @Flow\IgnoreValidation("$post")
	 * @return void
	 */
	public function editAction(\RobertLemke\Plugin\Blog\Domain\Model\Post $post) {
		$this->view->assign('blog', $this->blog);
			// Don't display the post we're editing in the related posts selector:
		$existingPosts = $this->postRepository->findRecentExceptThis($post);
		$this->view->assign('existingPosts', $existingPosts);
		$this->view->assign('categories', $this->categoryRepository->findAll());
		$this->view->assign('post', $post);
	}

	/**
	 * Set property mapper configuration for post update
	 *
	 * @return void
	 */
	public function initializeUpdateAction() {
		$this->arguments['post']->getPropertyMappingConfiguration()->allowModificationForSubProperty('image');
		$this->arguments['post']->getPropertyMappingConfiguration()->allowCreationForSubProperty('image');
	}

	/**
	 * Updates an existing post
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post Post containing the modifications
	 * @return void
	 */
	public function updateAction(\RobertLemke\Plugin\Blog\Domain\Model\Post $post) {
		$this->postRepository->update($post);
		$this->addFlashMessage('Your post has been updated.');
		$this->redirect('index');
	}

	/**
	 * Deletes an existing post
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post The post to remove
	 * @return void
	 */
	public function deleteAction(\RobertLemke\Plugin\Blog\Domain\Model\Post $post) {
		$this->postRepository->remove($post);
		$post->getBlog()->removePost($post);
		$this->blogRepository->update($this->blog);
		$this->addFlashMessage('The post has been deleted.');
		$this->redirect('index');
	}

	/**
	 * Override getErrorFlashMessage to present nice flash error messages.
	 *
	 * @return \TYPO3\Flow\Error\Message
	 */
	protected function getErrorFlashMessage() {
		switch ($this->actionMethodName) {
			case 'createAction' :
				return new \TYPO3\Flow\Error\Error('Could not create the new post');
			case 'updateAction' :
				return new \TYPO3\Flow\Error\Error('Could not update the post');
			default :
				return parent::getErrorFlashMessage();
		}
	}

	/**
	 * @return \TYPO3\Flow\Security\Account
	 */
	protected function findCurrentAccount() {
		$activeTokens = $this->securityContext->getAuthenticationTokens();
		foreach ($activeTokens as $token) {
			if ($token->isAuthenticated()) {
				return $token->getAccount();
			}
		}
		return NULL;
	}
}
?>