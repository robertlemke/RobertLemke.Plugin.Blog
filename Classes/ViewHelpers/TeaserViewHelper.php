<?php
declare(strict_types=1);

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

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception as ViewHelperException;
use RobertLemke\Plugin\Blog\Service\ContentService;

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
     * Initialize arguments
     *
     * @return void
     * @throws ViewHelperException
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('node', NodeInterface::class, 'Node to render the teaser for', true);
        $this->registerArgument('maximumLength', 'int', 'Maximum length of teaser', false, 500);
    }

    /**
     * Render a teaser
     *
     * @return string cropped text
     */
    public function render(): string
    {
        return $this->contentService->renderTeaser($this->arguments['node'], $this->arguments['maximumLength']);
    }
}
