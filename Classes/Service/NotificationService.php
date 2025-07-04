<?php
declare(strict_types=1);

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
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Log\ThrowableStorageInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\SymfonyMailer\Exception\InvalidMailerConfigurationException;
use Neos\SymfonyMailer\Service\MailerService;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use Psr\Log\LoggerInterface;

/**
 * A notification service
 */
#[Flow\Scope("singleton")]
class NotificationService
{
    protected array $settings;

    #[Flow\Inject]
    protected ThrowableStorageInterface $throwableStorage;

    #[Flow\Inject]
    protected LoggerInterface $logger;

    public function injectSettings(array $settings): void
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
    public function sendNewCommentNotification(NodeInterface $commentNode, NodeInterface $postNode): void
    {
        if ($this->settings['notifications']['to']['email'] === '') {
            return;
        }

        if (!class_exists(MailerService::class)) {
            $this->logger->info('The package "Neos.SymfonyMailer" is required to send notifications!', LogEnvironment::fromMethodName(__METHOD__));
            return;
        }

        try {
            $mailerService = new MailerService();

            // prepare email properties
            $subject = 'New comment on blog post "' . $postNode->getProperty('title') . '"' . ($commentNode->getProperty('spam') ? ' (SPAM)' : '');
            $from = new Address($this->settings['notifications']['to']['email'], $this->settings['notifications']['to']['name']);
            $reply = new Address($commentNode->getProperty('emailAddress'), $commentNode->getProperty('author'));

            // Send email to the blog administrator
            $email = new Email();
            $email
                ->from($from)
                ->replyTo($reply)
                ->to($from)
                ->subject($subject)
                ->text($commentNode->getProperty('text'));

            $mailer = $mailerService->getMailer();
            $mailer->send($email);
        } catch (TransportExceptionInterface|InvalidMailerConfigurationException|\Exception $e) {
            $message = $this->throwableStorage->logThrowable($e);
            $this->logger->error($message, LogEnvironment::fromMethodName(__METHOD__));
        }
    }
}
