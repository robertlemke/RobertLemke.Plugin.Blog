<?php
namespace RobertLemke\Plugin\Blog\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Blog".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Media\Domain\Model\ImageInterface;
use TYPO3\Media\ViewHelpers\ImageViewHelper;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * This view helper display the first image from the given node
 *
 * @api
 */
class ThumbnailViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	/**
	 * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
	 * @Flow\Inject
	 */
	protected $resourcePublisher;

	/**
	 * name of the tag to be created by this view helper
	 *
	 * @var string
	 */
	protected $tagName = 'img';

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', TRUE);
	}

	/**
	 * @Flow\Inject
	 * @var \RobertLemke\Plugin\Blog\Service\ContentService
	 */
	protected $contentService;

	/**
	 * Render a teaser
	 *
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $node
	 * @param integer $maximumWidth Desired maximum height of the image
	 * @param integer $maximumHeight Desired maximum width of the image
	 * @param boolean $allowCropping Whether the image should be cropped if the given sizes would hurt the aspect ratio
	 * @param boolean $allowUpScaling Whether the resulting image size might exceed the size of the original image
	 * @return string cropped text
	 */
	public function render(NodeInterface $node, $maximumWidth = NULL, $maximumHeight = NULL, $allowCropping = FALSE, $allowUpScaling = FALSE) {
		/** @var NodeInterface $image */
		$image = $this->contentService->extractFirstImage($node);
		if ($image === NULL || !$image->getProperty('image') instanceof ImageInterface ) {
			return;
		}
		$thumbnailImage = $this->getThumbnailImage($image->getProperty('image'), $maximumWidth, $maximumHeight, $allowCropping, $allowUpScaling);

		$this->tag->addAttributes(array(
			'width' => $thumbnailImage->getWidth(),
			'height' => $thumbnailImage->getHeight(),
			'src' => $this->resourcePublisher->getPersistentResourceWebUri($thumbnailImage->getResource()),
		));

		return $this->tag->render();
	}

	/**
	 * Calculates the dimensions of the thumbnail to be generated and returns the thumbnail image if the new dimensions
	 * differ from the specified image dimensions, otherwise the original image is returned.
	 *
	 * @param \TYPO3\Media\Domain\Model\ImageInterface $image
	 * @param integer $maximumWidth
	 * @param integer $maximumHeight
	 * @param boolean $allowCropping
	 * @param boolean $allowUpScaling
	 * @return \TYPO3\Media\Domain\Model\ImageInterface
	 *
	 * TODO move code to trait in order to avoid duplication with uri.image ViewHelper
	 */
	protected function getThumbnailImage(ImageInterface $image, $maximumWidth, $maximumHeight, $allowCropping, $allowUpScaling) {
		$ratioMode = ($allowCropping ? ImageInterface::RATIOMODE_OUTBOUND : ImageInterface::RATIOMODE_INSET);
		if ($maximumWidth === NULL || ($allowUpScaling !== TRUE && $maximumWidth > $image->getWidth())) {
			$maximumWidth = $image->getWidth();
		}
		if ($maximumHeight === NULL || ($allowUpScaling !== TRUE && $maximumHeight > $image->getHeight())) {
			$maximumHeight = $image->getHeight();
		}
		if ($maximumWidth === $image->getWidth() && $maximumHeight === $image->getHeight()) {
			return $image;
		}
		return $image->getThumbnail($maximumWidth, $maximumHeight, $ratioMode);
	}
}


?>