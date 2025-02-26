<?php
declare(strict_types=1);

namespace RobertLemke\Plugin\Blog\Runtime\Action;

use Neos\ContentRepository\Domain\NodeAggregate\NodeName;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\ContentRepository\Domain\Service\NodeService;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Domain\Utility\NodePaths;
use Neos\Fusion\Form\Runtime\Action\AbstractAction;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Fusion\Form\Runtime\Domain\Exception\ActionException;
use Neos\Neos\Controller\CreateContentContextTrait;
use RobertLemke\Akismet\Exception\ConnectionException;
use RobertLemke\Akismet\Service;


class CreateCommentAction extends AbstractAction
{
    use CreateContentContextTrait;

    #[Flow\Inject]
    protected Service $akismetService;

    #[Flow\Inject]
    protected NodeTypeManager $nodeTypeManager;

    #[Flow\Inject]
    protected NodeService $nodeService;

    /**
     * @return ActionResponse|null
     */
    public function perform(): ?ActionResponse
    {
        $response = new ActionResponse();

        $author = $this->options['author'] ?? null;
        $text = $this->options['text'] ?? null;
        $emailAddress = $this->options['emailAddress'] ?? null;
        $postNodePath = $this->options['postNode'] ?? null;

        $contentContext = $this->createContentContext('live', []);
        $postNode = $contentContext->getNode($postNodePath);

        if ($postNode === null) {
            throw new ActionException('Required parameters missing', 1740574436);
        }

        $textNodeType = $this->nodeTypeManager->getNodeType('RobertLemke.Plugin.Blog:Content.Comment');
        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($textNodeType);
        $nodeTemplate->setProperty('author', htmlspecialchars($author));
        $nodeTemplate->setProperty('text', htmlspecialchars($text));
        $nodeTemplate->setProperty('emailAddress', htmlspecialchars($emailAddress));
        $nodeTemplate->setProperty('spam', false);

        $commentNode = $postNode
            ->findNamedChildNode(NodeName::fromString('comments'))
            ->createNodeFromTemplate($nodeTemplate, NodePaths::generateRandomNodeName());

        $commentNode->setProperty('datePublished', new \DateTime());

        try {
            if ($this->akismetService->isCommentSpam('', $text, 'comment', $author, $emailAddress)) {
                $commentNode->setProperty('spam', true);
            }
        } catch (ConnectionException $e) {
            throw new ActionException('Akismet service not available', 1740575405);
        }

        $response->setContent($this->options['message']);
        return $response;
    }
}
