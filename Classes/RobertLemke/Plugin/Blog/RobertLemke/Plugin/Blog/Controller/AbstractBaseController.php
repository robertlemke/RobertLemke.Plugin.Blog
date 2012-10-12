<?php
namespace RobertLemke\Plugin\Blog\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Blog".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * An action controller with base functionality for all action controllers of
 * the Blog package.
 *
 */
abstract class AbstractBaseController extends \TYPO3\FLOW3\Mvc\Controller\ActionController {

	/**
	 * @FLOW3\Inject
	 * @var \RobertLemke\Plugin\Blog\Domain\Repository\BlogRepository
	 */
	protected $blogRepository;

	/**
	 * @FLOW3\Inject
	 * @var \RobertLemke\Plugin\Blog\Domain\Repository\PostRepository
	 */
	protected $postRepository;

	/**
	 * @var \RobertLemke\Plugin\Blog\Domain\Model\Blog
	 */
	protected $blog;

	/**
	 * Initializes the view before invoking an action method.
	 *
	 * @param \TYPO3\FLOW3\Mvc\View\ViewInterface $view The view to be initialized
	 * @return void
	 */
	protected function initializeView(\TYPO3\FLOW3\Mvc\View\ViewInterface $view) {
		$this->blog = $this->blogRepository->findActive();
		if ($this->blog === NULL) {
			$this->redirect('index', 'Setup');
		}
		$view->assign('blog', $this->blog);
	}

}

?>