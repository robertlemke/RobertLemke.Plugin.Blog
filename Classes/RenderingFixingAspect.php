<?php
namespace RobertLemke\Plugin\Blog;

/*
 * This file is part of the RobertLemke.Plugin.Blog package.
 *
 * (c) Robert Lemke
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;

/**
 * Aspect for hotfixing rendering issues with XML content
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class RenderingFixingAspect
{
    /**
     * Makes sure that XML content (such as the RSS feed) is not wrapped with divs.
     *
     * @param JoinPointInterface $joinPoint
     * @return string
     * @Flow\Around("method(Neos\Neos\Service\ContentElementWrappingService->wrapContentObject())")
     */
    public function preventContentElementWraps(JoinPointInterface $joinPoint)
    {
        $content = $joinPoint->getMethodArgument('content');
        if (substr($content, 0, 5) === '<?xml') {
            return $content;
        }

        return $joinPoint->getAdviceChain()->proceed($joinPoint);
    }
}
