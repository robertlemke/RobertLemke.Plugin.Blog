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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * The category controller for the Blog package
 *
 */
class CategoryController extends \RobertLemke\Plugin\Blog\Controller\AbstractBaseController {

	/**
	 * @FLOW3\Inject
	 * @var \RobertLemke\Plugin\Blog\Domain\Repository\CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * @FLOW3\Inject
	 * @var \RobertLemke\Plugin\Blog\Domain\Repository\PostRepository
	 */
	protected $postRepository;

	/**
	 * List action for this controller.
	 *
	 * @return string
	 */
	public function indexAction() {
		$this->view->assign('categories', $this->categoryRepository->findAll());
	}

	/**
	 * Creates a new category
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Category $newCategory A fresh category object which has not yet been added to the repository
	 * @return void
	 */
	public function createAction(\RobertLemke\Plugin\Blog\Domain\Model\Category $newCategory) {
		$this->categoryRepository->add($newCategory);
		$this->addFlashMessage('Your new category was created.');
		$this->redirect('index');
	}

	/**
	 * Displays a form for editing an existing category
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Category $category An existing category object taken as a basis for the rendering
	 * @return string An HTML form for editing a category
	 */
	public function editAction(\RobertLemke\Plugin\Blog\Domain\Model\Category $category) {
		$this->view->assign('category', $category);
	}

	/**
	 * Updates an existing category
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Category $category Category containing the modifications
	 * @return void
	 */
	public function updateAction(\RobertLemke\Plugin\Blog\Domain\Model\Category $category) {
		$this->categoryRepository->update($category);
		$this->addFlashMessage('Your category has been updated.');
		$this->redirect('index');
	}

	/**
	 * Deletes an existing category
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Category $category The category to remove
	 * @return void
	 */
	public function deleteAction(\RobertLemke\Plugin\Blog\Domain\Model\Category $category) {
		$numberOfPostsInThisCategory = $this->postRepository->countByCategory($category);
		if ($numberOfPostsInThisCategory > 0) {
			$this->addFlashMessage('%d post(s) refer to category "%s". Please adjust them first.', 'Category can\'t be deleted', \TYPO3\FLOW3\Error\Message::SEVERITY_ERROR, array($numberOfPostsInThisCategory, $category));
		} else {
			$this->categoryRepository->remove($category);
			$this->addFlashMessage('The category has been deleted.');
		}
		$this->redirect('index');
	}

	/**
	 * Override getErrorFlashMessage to present nice flash error messages.
	 *
	 * @return \TYPO3\FLOW3\Error\Message
	 */
	protected function getErrorFlashMessage() {
		switch ($this->actionMethodName) {
			case 'createAction' :
				return new \TYPO3\FLOW3\Error\Error('Could not create the new category');
			default :
				return parent::getErrorFlashMessage();
		}
	}
}
?>