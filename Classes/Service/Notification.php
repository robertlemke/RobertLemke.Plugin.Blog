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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A notification service
 *
 * @FLOW3\Scope("singleton")
 */
class Notification {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Log\SystemLoggerInterface
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
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Comment $comment
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post
	 * @return void
	 */
	public function sendNewCommentNotification(\RobertLemke\Plugin\Blog\Domain\Model\Comment $comment, \RobertLemke\Plugin\Blog\Domain\Model\Post $post) {
		if ($this->settings['notifications']['to']['email'] === '') {
			return;
		}

		try {
			$mail = new \TYPO3\SwiftMailer\Message();
			$mail
				->setFrom(array($comment->getEmailAddress() => $comment->getAuthor()))
				->setTo(array($this->settings['notifications']['to']['email'] => $this->settings['notifications']['to']['name']))
				->setSubject('New comment on blog post "' . $post->getTitle() . '"' . ($comment->isSpam() ? ' (SPAM)' : ''))
				->setBody($comment->getContent())
				->send();
		} catch (\Exception $e) {
			$this->systemLogger->logException($e);
		}
	}

}

?>