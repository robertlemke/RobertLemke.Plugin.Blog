<?php
namespace RobertLemke\Plugin\Blog\Service;

/*
 * This file is part of the RobertLemke.Plugin.Blog package.
 *
 * (c) Robert Lemke
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\SwiftMailer\Message;
use Neos\ContentRepository\Domain\Model\NodeInterface;

/**
 * A notification service
 *
 * @Flow\Scope("singleton")
 */
class NotificationService
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Send a new notification that a comment has been created
     *
     * @param NodeInterface $commentNode The comment node
     * @param NodeInterface $postNode The post node
     * @return void
     */
    public function sendNewCommentNotification(NodeInterface $commentNode, NodeInterface $postNode)
    {
        if ($this->settings['notifications']['to']['email'] === '') {
            return;
        }

        if (!class_exists('Neos\SwiftMailer\Message')) {
            $this->systemLogger->log('The package "Neos.SwiftMailer" is required to send notifications!');

            return;
        }

        try {
            $mail = new Message();
            $mail
                ->setFrom([$this->settings['notifications']['to']['email'] => $this->settings['notifications']['to']['name']])
                ->setReplyTo([$commentNode->getProperty('emailAddress') => $commentNode->getProperty('author')])
                ->setTo([$this->settings['notifications']['to']['email'] => $this->settings['notifications']['to']['name']])
                ->setSubject('New comment on blog post "' . $postNode->getProperty('title') . '"' . ($commentNode->getProperty('spam') ? ' (SPAM)' : ''))
                ->setBody($commentNode->getProperty('text'))
                ->send();
        } catch (\Exception $e) {
            $this->systemLogger->logException($e);
        }
    }
}
