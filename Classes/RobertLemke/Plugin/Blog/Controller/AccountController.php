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
 * The account controller for the Blog package
 *
 */
class AccountController extends \RobertLemke\Plugin\Blog\Controller\AbstractBaseController {

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
	 * @var \TYPO3\FLOW3\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * List action for this controller.
	 *
	 * @return string
	 */
	public function indexAction() {
		$this->forward('edit');
	}

	/**
	 * Displays a form for setting a new password and / or username
	 *
	 * @return string An HTML form for editing the account properties
	 */
	public function editAction() {
		$activeTokens = $this->securityContext->getAuthenticationTokens();
		foreach ($activeTokens as $token) {
			if ($token->isAuthenticated()) {
				$account = $token->getAccount();
				$this->view->assign('account', $account);
				break;
			}
		}
	}

	/**
	 * @return void
	 */
	public function initializeUpdateAction() {
		$this->arguments['account']->getPropertyMappingConfiguration()->setTargetTypeForSubProperty('party', 'TYPO3\Party\Domain\Model\Person');
		$this->arguments['account']->getPropertyMappingConfiguration()->allowModificationForSubProperty('party');
		$this->arguments['account']->getPropertyMappingConfiguration()->allowModificationForSubProperty('party.name');
	}

	/**
	 * Updates the account properties
	 *
	 * @param \TYPO3\FLOW3\Security\Account $account
	 * @param string $password
	 * @return void
	 */
	public function updateAction(\TYPO3\FLOW3\Security\Account $account, $password = '') {
		if ($password != '') {
			$account->setCredentialsSource($this->hashService->hashPassword($password));
		}

		$this->accountRepository->update($account);
		$this->partyRepository->update($account->getParty());
		$this->addFlashMessage('Your account details have been updated.');
		$this->redirect('index', 'Admin');
	}
}
?>