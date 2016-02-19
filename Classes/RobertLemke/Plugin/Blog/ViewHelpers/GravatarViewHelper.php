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

use TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

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
class GravatarViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'img';

    /**
     * Initialize arguments
     *
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('email', 'string', 'Gravatar Email', true);
        $this->registerArgument('default', 'string', 'Default URL if no gravatar was found');
        $this->registerArgument('size', 'Integer', 'Size of the gravatar');

        $this->registerUniversalTagAttributes();
    }

    /**
     * Render the link.
     *
     * @return string The rendered link
     */
    public function render()
    {
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
