<?php
namespace RobertLemke\Plugin\Blog\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "Blog".                      *
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
use TYPO3\SwiftMailer\Message;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * A notification service
 *
 * @Flow\Scope("singleton")
 */
class NotificationService {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Send a new notification that a comment has been created
	 *
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $commentNode The comment node
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $postNode The post node
	 * @return void
	 */
	public function sendNewCommentNotification(NodeInterface $commentNode, NodeInterface $postNode) {
		if ($this->settings['notifications']['to']['email'] === '') {
			return;
		}

		if (!class_exists('TYPO3\SwiftMailer\Message')) {
			$this->systemLogger->logException(new \TYPO3\Flow\Exception('The package "TYPO3.SwiftMailer" is required to send notifications!', 1359473932));
			return;
		}

		try {
			$mail = new Message();
			$mail
				->setFrom(array($this->settings['notifications']['to']['email'] => $this->settings['notifications']['to']['name']))
				->setReplyTo(array($commentNode->getProperty('emailAddress') => $commentNode->getProperty('author')))
				->setTo(array($this->settings['notifications']['to']['email'] => $this->settings['notifications']['to']['name']))
				->setSubject('New comment on blog post "' . $postNode->getProperty('title') . '"' . ($commentNode->getProperty('spam') ? ' (SPAM)' : ''))
				->setBody($commentNode->getProperty('text'))
				->send();
		} catch (\Exception $e) {
			$this->systemLogger->logException($e);
		}
	}

}

?>