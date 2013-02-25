<?php
namespace RobertLemke\Plugin\Blog\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Blog".                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A blog post comment
 *
 * @Flow\Entity
 */
class Comment {

	/**
	 * @var \DateTime
	 * @Flow\Identity
	 */
	protected $date;

	/**
	 * @var string
	 * @Flow\Validate(type="Text")
	 * @Flow\Validate(type="StringLength", options={ "minimum"=3, "maximum"=80 })
	 * @Flow\Identity
	 * @ORM\Column(length=80)
	 */
	protected $author;

	/**
	 * @var string
	 * @Flow\Validate(type="EmailAddress")
	 */
	protected $emailAddress;

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 * @ORM\Column(type="text")
	 */
	protected $content;

	/**
	 * If set, this comment has been suspected to be spam
	 * @var boolean
	 */
	protected $spam = FALSE;

	/**
	 * Constructs this comment
	 *
	 */
	public function __construct() {
		$this->date = new \DateTime();
	}

	/**
	 * Setter for date
	 *
	 * @param \DateTime $date
	 * @return void
	 */
	public function setDate(\DateTime $date) {
		$this->date = $date;
	}

	/**
	 * Getter for date
	 *
	 * @return \DateTime
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * Sets the author for this comment
	 *
	 * @param string $author
	 * @return void
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	/**
	 * Getter for author
	 *
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * Sets the authors email address for this comment
	 *
	 * @param string $emailAddress email address of the author
	 * @return void
	 */
	public function setEmailAddress($emailAddress) {
		$this->emailAddress = $emailAddress;
	}

	/**
	 * Getter for authors email address
	 *
	 * @return string
	 */
	public function getEmailAddress() {
		return $this->emailAddress;
	}

	/**
	 * Sets the content for this comment
	 *
	 * @param string $content
	 * @return void
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Getter for content
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Sets the spam flag
	 *
	 * @param boolean $flag
	 */
	public function setSpam($flag) {
		$this->spam = (boolean)$flag;
	}

	/**
	 * If this comment is considered spam
	 *
	 * @param boolean
	 */
	public function isSpam() {
		return $this->spam;
	}
}

?>