<?php
declare(strict_types=1);

namespace RobertLemke\Plugin\Blog\Controller;

/*
 * This file is part of the RobertLemke.Plugin.Blog package.
 *
 * (c) Robert Lemke
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\ContentRepository\Exception\NodeException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use RobertLemke\Akismet\Exception\ConnectionException;
use RobertLemke\Akismet\Service;

/**
 * Comments controller for the Blog package
 */
class CommentController extends ActionController
{
    /**
     * @Flow\Inject
     * @var Service
     */
    protected $akismetService;

    /**
     * Initialize the Akismet service
     *
     * @return void
     */
    protected function initializeAction()
    {
        $this->akismetService->setCurrentRequest($this->request->getHttpRequest());
    }

    /**
     * Creates a new comment
     *
     * @param NodeInterface $postNode The post node which will contain the new comment
     * @param NodeTemplate<RobertLemke.Plugin.Blog:Comment> $newComment
     * @return string
     * @throws NodeException
     * @throws ConnectionException
     */
    public function createAction(NodeInterface $postNode, NodeTemplate $newComment): string
    {
        # Workaround until we can validate node templates properly:
        if (strlen($newComment->getProperty('author')) < 2) {
            $this->throwStatus(400, 'Your comment was NOT created - please specify your name.');
        }

        if (filter_var($newComment->getProperty('emailAddress'), FILTER_VALIDATE_EMAIL) === false) {
            $this->throwStatus(400, 'Your comment was NOT created - you must specify a valid email address.');
        }

        if (strlen($newComment->getProperty('text')) < 5) {
            $this->throwStatus(400, 'Your comment was NOT created - it was too short.');
        }

        $newComment->setProperty('text', htmlspecialchars($newComment->getProperty('text')));
        $newComment->setProperty('author', htmlspecialchars($newComment->getProperty('author')));
        $newComment->setProperty('emailAddress', htmlspecialchars($newComment->getProperty('emailAddress')));

        $commentNode = $postNode->getNode('comments')->createNodeFromTemplate($newComment, uniqid('comment-', true));
        $commentNode->setProperty('spam', false);
        $commentNode->setProperty('datePublished', new \DateTime());

        if ($this->akismetService->isCommentSpam('', $commentNode->getProperty('text'), 'comment', $commentNode->getProperty('author'), $commentNode->getProperty('emailAddress'))) {
            $commentNode->setProperty('spam', true);
        }

        $this->emitCommentCreated($commentNode, $postNode);
        $this->response->setStatusCode(201);

        return 'Thank you for your comment!';
    }

    /**
     * Signal which informs about a newly created comment
     *
     * @param NodeInterface $commentNode The comment node
     * @param NodeInterface $postNode The post node
     * @return void
     * @Flow\Signal
     */
    protected function emitCommentCreated(NodeInterface $commentNode, NodeInterface $postNode): void
    {
    }
}
