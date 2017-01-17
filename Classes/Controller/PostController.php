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

use RobertLemke\Plugin\Blog\Service\ContentService;
use RobertLemke\Rss\Channel;
use RobertLemke\Rss\Feed;
use RobertLemke\Rss\Item;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Service;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;

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
     */
    public function rssAction()
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

        $feedTitle = $this->request->getInternalArgument('__feedTitle');
        $feedDescription = $this->request->getInternalArgument('__feedDescription');
        $includeContent = $this->request->getInternalArgument('__includeContent');

        if ($this->request->getInternalArgument('__feedUri')) {
            $feedUri = $this->request->getInternalArgument('__feedUri');
        } else {
            $uriBuilder->setFormat('xml');
            $feedUri = $uriBuilder->uriFor('show', ['node' => $rssDocumentNode], 'Frontend\Node', 'Neos.Neos');
        }

        $channel = new Channel();
        $channel->setTitle($feedTitle);
        $channel->setDescription($feedDescription);
        $channel->setFeedUri($feedUri);
        $channel->setWebsiteUri($this->request->getHttpRequest()->getBaseUri());
        $channel->setLanguage((string)$this->i18nService->getConfiguration()->getCurrentLocale());

        /* @var $postNode NodeInterface */
        foreach ($postsNode->getChildNodes('RobertLemke.Plugin.Blog:Post') as $postNode) {

            $uriBuilder->setFormat('html');
            $postUri = $uriBuilder->uriFor('show', ['node' => $postNode], 'Frontend\Node', 'Neos.Neos');

            $item = new Item();
            $item->setTitle($postNode->getProperty('title'));
            $item->setGuid($postNode->getIdentifier());
            $item->setPublicationDate($postNode->getProperty('datePublished'));
            $item->setItemLink((string)$postUri);
            $item->setCommentsLink((string)$postUri . '#comments');

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

        $headers = $this->response->getHeaders();
        $headers->setCacheControlDirective('s-max-age', 3600);
        $headers->set('Content-Type', 'application/rss+xml');

        $feed = new Feed();
        $feed->addChannel($channel);

        return $feed->render();
    }
}
