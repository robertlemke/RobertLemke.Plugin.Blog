<?php
namespace RobertLemke\Plugin\Blog;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;

/**
 * Aspect for hotfixing rendering issues with XML content
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class RenderingFixingAspect {

	/**
	 * Makes sure that XML content (such as the RSS feed) is not wrapped with divs.
	 *
	 * @param JoinPointInterface $joinPoint
	 * @return string
	 * @Flow\Around("method(TYPO3\Neos\Service\ContentElementWrappingService->wrapContentObject())")
	 */
	public function preventContentElementWraps(JoinPointInterface $joinPoint) {
		$content = $joinPoint->getMethodArgument('content');
		if (substr($content, 0, 5) === '<?xml') {
			return $content;
		}
		return $joinPoint->getAdviceChain()->proceed($joinPoint);
	}

}

?>