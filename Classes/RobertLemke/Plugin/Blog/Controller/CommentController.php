<?php
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

use RobertLemke\Akismet\Service;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;

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
     */
    public function createAction(NodeInterface $postNode, NodeTemplate $newComment)
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

        $commentNode = $postNode->getNode('comments')->createNodeFromTemplate($newComment, uniqid('comment-'));
        $commentNode->setProperty('spam', false);
        $commentNode->setProperty('datePublished', new \DateTime());

        if ($this->akismetService->isCommentSpam('', $commentNode->getProperty('text'), 'comment', $commentNode->getProperty('author'), $commentNode->getProperty('emailAddress'))) {
            $commentNode->setProperty('spam', true);
        }

        $this->emitCommentCreated($commentNode, $postNode);
        $this->response->setStatus(201);

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
    protected function emitCommentCreated(NodeInterface $commentNode, NodeInterface $postNode)
    {
    }
}
