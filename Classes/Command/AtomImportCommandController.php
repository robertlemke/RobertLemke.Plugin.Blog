<?php
declare(strict_types=1);

namespace RobertLemke\Plugin\Blog\Command;

/*
 * This file is part of the RobertLemke.Plugin.Blog package.
 *
 * (c) Robert Lemke
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\VisibilityConstraints;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeName;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Rector\ContentRepository90\Legacy\LegacyContextStub;

/**
 * BlogCommand command controller for the RobertLemke.Plugin.Blog package
 *
 * @Flow\Scope("singleton")
 */
class AtomImportCommandController extends CommandController
{
    protected Node $blogNode;

    protected array $tagNodes = [];

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    /**
     * Imports atom data into the blog
     *
     * @param string $workspace The workspace to work in
     * @param string $targetNode The target node (expressed as a FlowQuery find condition)
     * @param string $atomFile The atom file to import
     * @return void
     */
    public function migrateCommand(string $workspace, string $targetNode, string $atomFile): void
    {
        if (!class_exists(\SimplePie::class)) {
            $this->outputLine('The Atom import needs simplepie/simplepie, which you can install using composer.');
            $this->quit(1);
        }

        $context = new LegacyContextStub(['workspaceName' => $workspace]);
        // TODO 9.0 migration: !! MEGA DIRTY CODE! Ensure to rewrite this; by getting rid of LegacyContextStub.
        $contentRepository = $this->contentRepositoryRegistry->get(ContentRepositoryId::fromString('default'));
        $workspace = $contentRepository->findWorkspaceByName(WorkspaceName::fromString('live'));
        $rootNodeAggregate = $contentRepository->getContentGraph($workspace->workspaceName)->findRootNodeAggregateByType(NodeTypeName::fromString('Neos.Neos:Sites'));
        $subgraph = $contentRepository->getContentGraph($workspace->workspaceName)->getSubgraph(DimensionSpacePoint::fromLegacyDimensionArray($context->dimensions ?? []), $context->invisibleContentShown ? VisibilityConstraints::withoutRestrictions() : VisibilityConstraints::default());
        $q = new FlowQuery([$subgraph->findNodeById($rootNodeAggregate->nodeAggregateId)]);
        $this->blogNode = $q->find($targetNode)->get(0);
        if (!($this->blogNode instanceof Node)) {
            $this->outputLine('<error>Target node not found.</error>');
            $this->quit(1);
        }

        $parser = new \SimplePie();
        $parser->enable_order_by_date();
        $parser->enable_cache(false);

        $parser->set_raw_data(file_get_contents($atomFile));
        $parser->strip_attributes();
        $parser->strip_htmltags(array_merge($parser->strip_htmltags, ['span']));
        $parser->init();
        $items = $parser->get_items();

        $comments = [];
        /** @var \SimplePie_Item $item */
        foreach ($items as $item) {
            $categories = $item->get_categories();

            if (!is_array($categories)) {
                continue;
            }

            /** @var \SimplePie_Category $category */
            foreach ($categories as $category) {
                if ($category->get_term() === 'http://schemas.google.com/blogger/2008/kind#comment') {
                    $inReplyTo = current($item->get_item_tags('http://purl.org/syndication/thread/1.0', 'in-reply-to'));
                    $inReplyTo = current($inReplyTo['attribs']);
                    $comments[$inReplyTo['ref']][$item->get_date('U')] = $item;
                }
            }
        }
        $contentRepository = $this->contentRepositoryRegistry->get(ContentRepositoryId::fromString('default'));

        $textNodeType = $contentRepository->getNodeTypeManager()->getNodeType('Neos.NodeTypes:Text');
        $commentNodeType = $contentRepository->getNodeTypeManager()->getNodeType('RobertLemke.Plugin.Blog:Content.Comment');
        $counter = 0;
        foreach ($parser->get_items() as $item) {
            $categories = $item->get_categories();
            if (!is_array($categories)) {
                continue;
            }

            $tags = [];
            $itemIsPost = false;
            foreach ($categories as $category) {
                if ($category->get_term() === 'http://schemas.google.com/blogger/2008/kind#post') {
                    $itemIsPost = true;
                }
                if ($category->get_scheme() === 'http://www.blogger.com/atom/ns#') {
                    $tags[] = $category->get_term();
                }
            }
            if (!$itemIsPost) {
                continue;
            }

            // TODO 9.0 migration: !! NodeTemplate is removed in Neos 9.0. Use the "CreateNodeAggregateWithNode" command to create new nodes or "CreateNodeVariant" command to create variants of an existing node in other dimensions.
            $nodeTemplate = new NodeTemplate();
            $nodeTemplate->setNodeType($contentRepository->getNodeTypeManager()->getNodeType('RobertLemke.Plugin.Blog:Document.Post'));
            $nodeTemplate->setProperty('title', $item->get_title());
            $nodeTemplate->setProperty('author', $item->get_author()->get_name());
            $published = new \DateTime();
            $published->setTimestamp($item->get_date('U'));
            $nodeTemplate->setProperty('datePublished', $published);
            $nodeTemplate->setProperty('tags', $this->getTagNodes($tags));

            $slug = strtolower(str_replace([' ', ',', ':', 'ü', 'à', 'é', '?', '!', '[', ']', '.', '\''], ['-', '', '', 'u', 'a', 'e', '', '', '', '', '-', ''], $item->get_title()));
            /** @var Node $postNode */
            $postNode = $this->blogNode->createNodeFromTemplate($nodeTemplate, $slug);
            $postNode->getNode('main')->createNode(uniqid('node'), $textNodeType)->setProperty('text', $item->get_content());

            $postComments = $comments[$item->get_id()] ?? [];
            if ($postComments !== []) {
                /** @var Node $commentsNode */
                $commentsNode = $postNode->getNode('comments');
                /** @var \SimplePie_Item $postComment */
                foreach ($postComments as $postComment) {
                    $commentNode = $commentsNode->createNode(uniqid('comment-', true), $commentNodeType);
                    $commentNode->setProperty('author', html_entity_decode($postComment->get_author()->get_name(), ENT_QUOTES, 'utf-8'));
                    $commentNode->setProperty('emailAddress', $postComment->get_author()->get_email());
                    $commentNode->setProperty('uri', $postComment->get_author()->get_link());
                    $commentNode->setProperty('datePublished', new \DateTime($postComment->get_date()));
                    $commentText = preg_replace('/<br[ \/]*>/i', chr(10), $postComment->get_content());
                    $commentText = html_entity_decode($commentText, ENT_QUOTES, 'utf-8');
                    $commentNode->setProperty('text', $commentText);
                    $commentNode->setProperty('spam', false);
                    $previousCommentNode = $commentNode;
                    $commentNode->moveAfter($previousCommentNode);
                }
            }

            $counter++;
            $this->outputLine($postNode->getProperty('title') . ' by ' . $postNode->getProperty('author'));
        }

        $this->outputLine('Imported %s blog posts.', [$counter]);
    }

    /**
     * @param array $tags
     * @return array<Node>
     */
    protected function getTagNodes(array $tags): array
    {
        $tagNodes = [];

        foreach ($tags as $tag) {
            if (!isset($this->tagNodes[$tag])) {
                $contentRepository = $this->contentRepositoryRegistry->get(ContentRepositoryId::fromString('default'));
                $tagNodeType = $contentRepository->getNodeTypeManager()->getNodeType('RobertLemke.Plugin.Blog:Document.Tag');

                $tagNode = $this->blogNode->createNode(NodeName::fromString($tag)->value, $tagNodeType);
                $tagNode->setProperty('title', $tag);
                $this->tagNodes[$tag] = $tagNode;
            }

            $tagNodes[] = $this->tagNodes[$tag];
        }

        return $tagNodes;
    }
}
