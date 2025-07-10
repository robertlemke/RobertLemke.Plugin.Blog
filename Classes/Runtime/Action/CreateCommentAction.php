<?php
declare(strict_types=1);

namespace RobertLemke\Plugin\Blog\Runtime\Action;

use Neos\ContentRepository\Core\DimensionSpace\OriginDimensionSpacePoint;
use Neos\ContentRepository\Core\Feature\NodeCreation\Command\CreateNodeAggregateWithNode;
use Neos\ContentRepository\Core\Feature\NodeModification\Dto\PropertyValuesToWrite;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeName;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Fusion\Form\Runtime\Action\AbstractAction;
use Neos\Fusion\Form\Runtime\Domain\Exception\ActionException;
use RobertLemke\Akismet\Exception\ConnectionException;
use RobertLemke\Akismet\Service;


class CreateCommentAction extends AbstractAction
{
    #[Flow\Inject]
    protected Service $akismetService;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    /**
     * @return ActionResponse|null
     * @throws ActionException
     */
    public function perform(): ?ActionResponse
    {
        $author = $this->options['author'] ?? null;
        $text = $this->options['text'] ?? null;
        $emailAddress = $this->options['emailAddress'] ?? null;
        $postNodePath = $this->options['postNode'] ?? null;

        // TODO 9.0 migration: !! CreateContentContextTrait::createContentContext() is removed in Neos 9.0.
        $contentContext = $this->createContentContext('live', []);
        $postNode = $contentContext->getNode($postNodePath);

        if ($postNode === null) {
            throw new ActionException('Required parameters missing', 1740574436);
        }

        $isSpam = false;
        try {
            if ($this->akismetService->isCommentSpam('', $text, 'comment', $author, $emailAddress)) {
                $isSpam = true;
            }
        } catch (ConnectionException $e) {
            throw new ActionException('Akismet service not available', 1740575405, $e);
        }

        $contentRepository = $this->contentRepositoryRegistry->get(ContentRepositoryId::fromString('default'));
        $textNodeType = $contentRepository->getNodeTypeManager()->getNodeType('RobertLemke.Plugin.Blog:Content.Comment');
        $commentsNode = $postNode->findNamedChildNode(NodeName::fromString('comments'));

        // TODO 9.0 migration: Fix this for real
        $contentRepository->handle(CreateNodeAggregateWithNode::create(
            WorkspaceName::forLive(),
            NodeAggregateId::create(),
            $textNodeType?->name,
            OriginDimensionSpacePoint::fromDimensionSpacePoint($arbitraryRootDimensionSpacePoint),
            $commentsNode->aggregateId,
            null,
            PropertyValuesToWrite::fromArray([
                'author' => htmlspecialchars($author),
                'text' => htmlspecialchars($text),
                'emailAddress' => htmlspecialchars($emailAddress),
                'spam' => $isSpam,
                'datePublished' => new \DateTime(),
            ])
        ));

        $this->emitCommentCreated(
            [
                'author' => htmlspecialchars($author),
                'text' => htmlspecialchars($text),
                'emailAddress' => htmlspecialchars($emailAddress),
                'spam' => $isSpam,
                'datePublished' => new \DateTime(),
            ],
            $postNode
        );

        $response = new ActionResponse();
        $response->setContent($this->options['message']);
        return $response;
    }

    #[Flow\Signal]
    protected function emitCommentCreated(array $comment, Node $postNode): void
    {
    }
}
