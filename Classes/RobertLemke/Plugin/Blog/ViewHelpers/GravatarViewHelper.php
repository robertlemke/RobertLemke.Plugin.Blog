<?php
namespace RobertLemke\Plugin\Blog\ViewHelpers;

/*                                                                         *
 * This script belongs to the TYPO3 Flow package "RobertLemke.Plugin.Blog" *
 *                                                                         *
 * It is free software; you can redistribute it and/or modify it under     *
 * the terms of the GNU General Public License, either version 3 of the    *
 * License, or (at your option) any later version.                         *
 *                                                                         *
 * The TYPO3 project - inspiring people to share!                          *
 *                                                                         */

/**
 * A view helper to display a Gravatar
 *
 * = Examples =
 *
 * <code title="Simple">
 * <blog:gravatar email="{emailAddress}" default="http://domain.com/gravatar_default.gif" class="gravatar" />
 * </code>
 *
 * Output:
 * <img class="gravatar" src="http://www.gravatar.com/avatar/<hash>?d=http%3A%2F%2Fdomain.com%2Fgravatar_default.gif" />
 *
 */
class GravatarViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'img';

	/**
	 * Initialize arguments
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('email', 'string', 'Gravatar Email', TRUE);
		$this->registerArgument('default', 'string', 'Default URL if no gravatar was found');
		$this->registerArgument('size', 'Integer', 'Size of the gravatar');

		$this->registerUniversalTagAttributes();
	}

	/**
	 * Render the link.
	 *
	 * @return string The rendered link
	 */
	public function render() {
		$sanitizedEmail = strtolower(trim((string)$this->arguments['email']));
		$gravatarUri = 'http://www.gravatar.com/avatar/' . md5($sanitizedEmail);
		$uriParts = array();
		if ($this->arguments['default']) {
			$uriParts[] = 'd=' . urlencode($this->arguments['default']);
		}
		if ($this->arguments['size']) {
			$uriParts[] = 's=' . $this->arguments['size'];
		}
		if (count($uriParts)) {
			$gravatarUri .= '?' . implode('&', $uriParts);
		}

		$this->tag->addAttribute('src', $gravatarUri);
		return $this->tag->render();
	}
}
