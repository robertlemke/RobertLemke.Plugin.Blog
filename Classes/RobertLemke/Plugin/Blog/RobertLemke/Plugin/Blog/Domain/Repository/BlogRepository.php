<?php
namespace RobertLemke\Plugin\Blog\Domain\Repository;

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
 * A repository for Blogs
 *
 * @Flow\Scope("singleton")
 */
class BlogRepository extends \TYPO3\Flow\Persistence\Repository {

	/**
	 * @Flow\Inject
	 * @var \RobertLemke\Plugin\Blog\Domain\Repository\PostRepository
	 */
	protected $postRepository;

	/**
	 * @Flow\Inject
	 * @var \RobertLemke\Plugin\Blog\Domain\Repository\CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * Remove the blog's posts before removing the blog itself.
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Blog $blog
	 * @return void
	 */
	public function remove($blog) {
		foreach ($blog->getPosts() as $post) {
			$this->postRepository->remove($post);
		}
		parent::remove($blog);
	}

	/**
	 * Remove all posts before removing all blogs
	 *
	 * @return void
	 */
	public function removeAll() {
		$this->categoryRepository->removeAll();
		$this->postRepository->removeAll();
		parent::removeAll();
	}

	/**
	 * Finds the active blog.
	 *
	 * As of now only one Blog is supported anyway so we just assume that only one
	 * Blog object resides in the BlogRepository.
	 *
	 * @return \RobertLemke\Plugin\Blog\Domain\Model\Blog The active blog or FALSE if none exists
	 */
	public function findActive() {
		$query = $this->createQuery();
		return $query->execute()->getFirst();
	}
}
?>