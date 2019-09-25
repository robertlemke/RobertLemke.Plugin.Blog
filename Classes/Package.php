<?php
declare(strict_types=1);

namespace RobertLemke\Plugin\Blog;

/*
 * This file is part of the RobertLemke.Plugin.Blog package.
 *
 * (c) Robert Lemke
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;
use RobertLemke\Plugin\Blog\Controller\CommentController;
use RobertLemke\Plugin\Blog\Service\NotificationService;

/**
 * The Blog Package
 *
 */
class Package extends BasePackage
{
    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(CommentController::class, 'commentCreated', NotificationService::class, 'sendNewCommentNotification');
    }
}
