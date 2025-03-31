<?php
declare(strict_types=1);

namespace RobertLemke\Plugin\Blog\Eel\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\ProtectedContextAwareInterface;
use RobertLemke\Plugin\Blog\Service\ContentService;

class Teaser implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var ContentService
     */
    protected $contentService;

    public function getCroppedTeaser(NodeInterface $node, int $maximumLength = 500): string
    {
        return $this->contentService->renderTeaser($node, $maximumLength);
    }

    /**
     * All methods are considered safe, i.e. can be executed from within Eel
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
