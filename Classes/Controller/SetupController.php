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
 * The setup controller for the Blog package, currently just setting up some
 * data to play with.
 *
 */
class SetupController extends \TYPO3\FLOW3\Mvc\Controller\ActionController {

	/**
	 * @FLOW3\Inject
	 * @var \RobertLemke\Plugin\Blog\Domain\Repository\BlogRepository
	 */
	protected $blogRepository;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Party\Domain\Repository\PartyRepository
	 */
	protected $partyRepository;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\AccountFactory
	 */
	protected $accountFactory;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * Sets up a fresh blog and creates a new user account.
	 *
	 * @return void
	 */
	public function indexAction() {
		if ($this->blogRepository->findActive() !== NULL) {
			$this->redirect('index', 'Post');
		}

		$this->blogRepository->removeAll();

		$blog = new \RobertLemke\Plugin\Blog\Domain\Model\Blog();
		$blog->setTitle('My Blog');
		$blog->setDescription('A blog about Foo, Bar and Baz.');
		$this->blogRepository->add($blog);

		$this->authenticationManager->logout();
		$this->accountRepository->removeAll();

		$account = $this->accountFactory->createAccountWithPassword('editor', 'joh316', array('Editor'));
		$this->accountRepository->add($account);

		$personName = new \TYPO3\Party\Domain\Model\PersonName('', 'First', '', 'Last');
		$person = new \TYPO3\Party\Domain\Model\Person();
		$person->setName($personName);
		$person->addAccount($account);
		$this->partyRepository->add($person);

		$authenticationTokens = $this->securityContext->getAuthenticationTokensOfType('TYPO3\FLOW3\Security\Authentication\Token\UsernamePassword');
		if (count($authenticationTokens) === 1) {
			$authenticationTokens[0]->setAccount($account);
			$authenticationTokens[0]->setAuthenticationStatus(\TYPO3\FLOW3\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL);
		}

		$this->redirect('edit', 'Account');
	}

}

?>