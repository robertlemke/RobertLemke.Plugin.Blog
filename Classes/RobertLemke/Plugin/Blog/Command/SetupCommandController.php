<?php
namespace RobertLemke\Plugin\Blog\Command;

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
 * The setup controller for the Blog package, for setting up some
 * data to play with.
 *
 * @Flow\Scope("singleton")
 */
class SetupCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \RobertLemke\Plugin\Blog\Domain\Repository\BlogRepository
	 */
	protected $blogRepository;

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
	 * @Flow\Inject
	 * @var \TYPO3\Party\Domain\Repository\PartyRepository
	 */
	protected $partyRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountFactory
	 */
	protected $accountFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;

	/**
	 * Create an editor account
	 *
	 * Creates a new account which has editor rights.
	 *
	 * @param string $identifier Account identifier, aka "user name"
	 * @param string $password Plain text password for the new account
	 * @param string $firstName First name of the account's holder
	 * @param string $lastName Last name of the account's holder
	 * @return void
	 */
	public function createAccountCommand($identifier, $password, $firstName, $lastName) {
		$account = $this->accountFactory->createAccountWithPassword($identifier, $password, array('Editor'));
		$this->accountRepository->add($account);

		$personName = new \TYPO3\Party\Domain\Model\PersonName('', $firstName, '', $lastName);
		$person = new \TYPO3\Party\Domain\Model\Person();
		$person->setName($personName);
		$person->addAccount($account);
		$this->partyRepository->add($person);

		$this->outputLine('The account "%s" was created.', array($identifier));
	}

	/**
	 * Create dummy posts and comments
	 *
	 * Sets up a a blog with a lot of posts and comments which is a nice test bed
	 * for profiling.
	 * You can set the "--force" flag in order to force deletion of any existing Blogs and Accounts
	 * Note: This command requires the TYPO3.Faker package to be installed
	 *
	 * @param integer $postCount Number of posts to generate
	 * @param integer $tagCount Number of tags to generate
	 * @param integer $categoryCount Number of categories to generate
	 * @param boolean $force Set this flag to generate override any existing blogs
	 * @return string
	 */
	public function profilingDataCommand($postCount = 250, $tagCount = 5, $categoryCount = 5, $force = FALSE) {
		if (!class_exists('TYPO3\Faker\Lorem')) {
			$this->outputLine('The required package TYPO3.Faker is not active.');
			$this->quit(1);
		}
		if ($force !== TRUE && $this->blogRepository->countAll() > 0) {
			$this->outputLine('There are blogs in the system. Set the --force option to DELETE all blogs and accounts.');
			$this->quit(1);
		}
		$this->blogRepository->removeAll();

		$commentCount = 0;

		$blog = new \RobertLemke\Plugin\Blog\Domain\Model\Blog();
		$blog->setTitle(ucwords(\TYPO3\Faker\Lorem::sentence(3)));
		$blog->setDescription(\TYPO3\Faker\Lorem::sentence(8));
		$this->blogRepository->add($blog);

		$authors = array();
		for ($i = 0; $i < 10; $i++) {
			$authors[] = \TYPO3\Faker\Name::fullName();
		}

		$tags = array();
		for ($i = 0; $i < $tagCount; $i++) {
			$tags[] = new \RobertLemke\Plugin\Blog\Domain\Model\Tag(\TYPO3\Faker\Lorem::words(1));
		}

		$categories = array();
		for ($i = 0; $i < $categoryCount; $i++) {
			$category = new \RobertLemke\Plugin\Blog\Domain\Model\Category();
			$category->setName(\TYPO3\Faker\Lorem::words(1));
			$categories[] = $category;
			$this->categoryRepository->add($category);
		}

		for ($i = 0; $i < $postCount; $i++) {
			$post = new \RobertLemke\Plugin\Blog\Domain\Model\Post();
			$post->setAuthor($authors[array_rand($authors)]);
			$post->setTitle(trim(\TYPO3\Faker\Lorem::sentence(3), '.'));
			$post->setContent(implode(chr(10),\TYPO3\Faker\Lorem::paragraphs(5)));
			$post->addTag($tags[array_rand($tags)]);
			$post->setCategory($categories[array_rand($categories)]);
			$post->setDate(\TYPO3\Faker\Date::random('- 500 days', '+0 seconds'));
			for ($j = 0; $j < rand(0, 10); $j++) {
				$comment = new \RobertLemke\Plugin\Blog\Domain\Model\Comment();
				$comment->setAuthor(\TYPO3\Faker\Name::fullName());
				$comment->setEmailAddress(\TYPO3\Faker\Internet::email());
				$comment->setContent(implode(chr(10),\TYPO3\Faker\Lorem::paragraphs(2)));
				$comment->setDate(\TYPO3\Faker\Date::random('+ 10 minutes', '+ 6 weeks', $post->getDate()));
				$post->addComment($comment);
				$commentCount++;
			}
			$this->postRepository->add($post);
			$blog->addPost($post);
		}
		$this->accountRepository->removeAll();

		$account = $this->accountFactory->createAccountWithPassword('editor', 'joh316', array('Editor'));
		$this->accountRepository->add($account);

		$personName = new \TYPO3\Party\Domain\Model\PersonName('', \TYPO3\Faker\Name::firstName(), '', \TYPO3\Faker\Name::lastName());
		$person = new \TYPO3\Party\Domain\Model\Person();
		$person->setName($personName);
		$person->addAccount($account);
		$this->partyRepository->add($person);

		$this->outputLine('Done, created 1 blog, %d posts, %d comments.', array($postCount, $commentCount));
	}
}
?>