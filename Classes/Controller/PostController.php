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
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Exception\NodeException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Component\SetHeaderComponent;
use Neos\Flow\Http\Helper\RequestInformationHelper;
use Neos\Flow\I18n\Service;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Flow\Mvc\Routing\UriBuilder;
use RobertLemke\Plugin\Blog\Service\ContentService;
use RobertLemke\Rss\Channel;
use RobertLemke\Rss\Feed;
use RobertLemke\Rss\Item;

/**
 * The posts controller for the Blog package
 *
 * @Flow\Scope("singleton")
 */
class PostController extends ActionController
{
    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var Service
     */
    protected $i18nService;

    /**
     * @Flow\Inject
     * @var ContentService
     */
    protected $contentService;

    /**
     * Renders an RSS feed
     *
     * @return string
     * @throws NodeException
     * @throws MissingActionNameException
     */
    public function rssAction(): string
    {
        $rssDocumentNode = $this->request->getInternalArgument('__documentNode');
        if ($rssDocumentNode === null) {
            return 'Error: The Blog Post Plugin cannot determine the current document node. Please make sure to include this plugin only by inserting it into a page / document.';
        }

        /** @var NodeInterface $postsNode */
        $postsNode = $this->request->getInternalArgument('__postsNode');

        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($this->request->getMainRequest());
        $uriBuilder->setCreateAbsoluteUri(true);
        $uriBuilder->setFormat('html');

        $feedTitle = $this->request->getInternalArgument('__feedTitle');
        $feedDescription = $this->request->getInternalArgument('__feedDescription');
        $includeContent = $this->request->getInternalArgument('__includeContent');

        if ($this->request->getInternalArgument('__feedUri')) {
            $feedUri = $this->request->getInternalArgument('__feedUri');
        } else {
            $uriBuilder->setFormat('xml');
            $feedUri = $uriBuilder->uriFor('show', ['node' => $rssDocumentNode], 'Frontend\Node', 'Neos.Neos');
            $uriBuilder->setFormat('html');
        }

        $channel = new Channel();
        $channel->setTitle($feedTitle)
            ->setDescription($feedDescription)
            ->setFeedUri($feedUri)
            ->setWebsiteUri((string)RequestInformationHelper::generateBaseUri($this->request->getHttpRequest()))
            ->setLanguage((string)$this->i18nService->getConfiguration()->getCurrentLocale());

        /* @var $postNode NodeInterface */
        foreach ($postsNode->getChildNodes('RobertLemke.Plugin.Blog:Post') as $postNode) {
            $postUri = $uriBuilder->uriFor('show', ['node' => $postNode], 'Frontend\Node', 'Neos.Neos');

            $item = new Item();
            $item->setTitle($postNode->getProperty('title'))
                ->setGuid($postNode->getIdentifier())
                ->setPublicationDate($postNode->getProperty('datePublished'))
                ->setItemLink((string)$postUri)
                ->setCommentsLink((string)$postUri . '#comments');

            $author = $postNode->getProperty('author');
            if ($author instanceof NodeInterface) {
                $author = $author->getLabel();
            }
            $item->setCreator($author);

            if ($postNode->getProperty('categories')) {
                $categories = [];
                /** @var NodeInterface $categoryNode */
                foreach ($postNode->getProperty('categories') as $categoryNode) {
                    $categories[] = [
                        'category' => $categoryNode->getProperty('title'),
                        'domain' => $categoryNode->getProperty('domain')
                    ];
                }
                $item->setCategories($categories);
            }

            $description = $this->contentService->renderTeaser($postNode);
            $item->setDescription($description);

            if ($includeContent) {
                $item->setContent($this->contentService->renderContent($postNode));
            }

            $channel->addItem($item);
        }

        $this->response->setHttpHeader('Cache-Control', 's-max-age=3600');
        $this->response->setContentType('application/rss+xml');

        $feed = new Feed();
        $feed->addChannel($channel);

        return $feed->render();
    }
}
