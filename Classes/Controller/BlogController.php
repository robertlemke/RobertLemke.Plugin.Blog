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

/**
 * The blog controller for the Blog package
 *
 */
class BlogController extends \RobertLemke\Plugin\Blog\Controller\AbstractBaseController {

	/**
	 * List action for this controller.
	 *
	 * @return string
	 */
	public function indexAction() {
		$this->forward('edit');
	}

	/**
	 * Displays a form for editing the properties of the blog
	 *
	 * @return string An HTML form for editing the blog properties
	 */
	public function editAction() {
	}

	/**
	 * Updates the blog properties
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Blog $blog  Blog containing the modifications
	 * @return void
	 */
	public function updateAction(\RobertLemke\Plugin\Blog\Domain\Model\Blog $blog) {
		$this->blogRepository->update($blog);
		$this->addFlashMessage('Your blog details have been updated.');
		$this->redirect('edit', 'Blog');
	}
}
?>