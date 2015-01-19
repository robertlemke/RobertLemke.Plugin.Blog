<?php
namespace RobertLemke\Plugin\Blog\Service;

/*                                                                         *
 * This script belongs to the TYPO3 Flow package "RobertLemke.Plugin.Blog" *
 *                                                                         *
 * It is free software; you can redistribute it and/or modify it under     *
 * the terms of the MIT License.                                           *
 *                                                                         *
 * The TYPO3 project - inspiring people to share!                          *
 *                                                                         */

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
