<?php
namespace RobertLemke\Plugin\Blog\ViewHelpers;

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
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * This view helper crops the text of a blog post in a meaningful way.
 *
 * @api
 */
class TeaserViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @Flow\Inject
     * @var ContentService
     */
    protected $contentService;

    /**
     * Render a teaser
     *
     * @param NodeInterface $node
     * @param integer $maximumLength
     * @return string cropped text
     */
    public function render(NodeInterface $node, $maximumLength = 500)
    {
        return $this->contentService->renderTeaser($node, $maximumLength);
    }
}
