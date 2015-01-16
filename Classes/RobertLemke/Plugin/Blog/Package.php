<?php
namespace RobertLemke\Plugin\Blog;

/*                                                                         *
 * This script belongs to the TYPO3 Flow package "RobertLemke.Plugin.Blog" *
 *                                                                         *
 * It is free software; you can redistribute it and/or modify it under     *
 * the terms of the MIT License.                                           *
 *                                                                         *
 * The TYPO3 project - inspiring people to share!                          *
 *                                                                         */

use \TYPO3\Flow\Package\Package as BasePackage;

/**
 * The Blog Package
 *
 */
class Package extends BasePackage {

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$dispatcher->connect('RobertLemke\Plugin\Blog\Controller\CommentController', 'commentCreated', 'RobertLemke\Plugin\Blog\Service\NotificationService', 'sendNewCommentNotification');
	}

}
