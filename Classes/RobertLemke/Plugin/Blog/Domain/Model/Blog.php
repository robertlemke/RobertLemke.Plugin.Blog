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
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A blog
 *
 * @FLOW3\Entity
 */
class Blog {

	/**
	 * The blog's title.
	 *
	 * @var string
	 * @FLOW3\Validate(type="Text")
	 * @FLOW3\Validate(type="StringLength", options={ "minimum"=1, "maximum"=80 })
	 * @ORM\Column(length=80)
	 */
	protected $title = '';

	/**
	 * A short description of the blog
	 *
	 * @var string
	 * @FLOW3\Validate(type="Text")
	 * @FLOW3\Validate(type="StringLength", options={ "maximum"=150 })
	 * @ORM\Column(length=150)
	 */
	protected $description = '';

	/**
	 * A longer description of the blog, mainly used in the meta tag for search engines
	 *
	 * @var string
	 * @FLOW3\Validate(type="Text")
	 * @FLOW3\Validate(type="StringLength", options={ "maximum"=255 })
	 */
	protected $fullDescription = '';

	/**
	 * A comma separated list of keywords, to be used in the meta information
	 *
	 * @var string
	 * @FLOW3\Validate(type="Text")
	 * @FLOW3\Validate(type="StringLength", options={ "maximum"=255 })
	 */
	protected $keywords = '';

	/**
	 * A short blurb about the blog or author
	 *
	 * @var string
	 * @ORM\Column(type="text", length=400)
	 * @FLOW3\Validate(type="Text")
	 * @FLOW3\Validate(type="StringLength", options={ "maximum"=400 })
	 */
	protected $blurb = '';

	/**
	 * A picture of the author
	 *
	 * @var \TYPO3\FLOW3\Resource\Resource
	 * @ORM\ManyToOne
	 */
	protected $authorPicture;

	/**
	 * Twitter username - if any
	 *
	 * @FLOW3\Validate(type="Text")
	 * @FLOW3\Validate(type="StringLength", options={ "maximum"=80 })
	 * @var string
	 * @ORM\Column(length=80)
	 */
	protected $twitterUsername = '';

	/**
	 * Google Analytics account number - if any
	 *
	 * @FLOW3\Validate(type="Text")
	 * @FLOW3\Validate(type="StringLength", options={ "maximum"=20 })
	 * @var string
	 * @ORM\Column(length=20)
	 */
	protected $googleAnalyticsAccountNumber = '';

	/**
	 * The posts contained in this blog
	 *
	 * @var \Doctrine\Common\Collections\Collection<\RobertLemke\Plugin\Blog\Domain\Model\Post>
	 * @ORM\OneToMany(mappedBy="blog")
	 * @ORM\OrderBy({"date" = "DESC"})
	 */
	protected $posts;

	/**
	 * Constructs a new Blog
	 */
	public function __construct() {
		$this->posts = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Sets this blog's title
	 *
	 * @param string $title The blog's title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Returns the blog's title
	 *
	 * @return string The blog's title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the description for the blog
	 *
	 * @param string $description The blog description or "tag line"
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * Returns the description
	 *
	 * @return string The blog description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Returns the blurb of this blog
	 *
	 * @return string
	 */
	public function getBlurb() {
		return $this->blurb;
	}

	/**
	 * Sets the blurb for this blog
	 *
	 * @param string $blurb
	 */
	public function setBlurb($blurb) {
		$this->blurb = $blurb;
	}

	/**
	 * Returns the author's picture
	 *
	 * @return \TYPO3\FLOW3\Resource\Resource
	 */
	public function getAuthorPicture() {
		return $this->authorPicture;
	}

	/**
	 * Sets the author's picture
	 *
	 * @param \TYPO3\FLOW3\Resource\Resource $authorPicture
	 * @return void
	 */
	public function setAuthorPicture(\TYPO3\FLOW3\Resource\Resource $authorPicture) {
		$this->authorPicture = $authorPicture;
	}

	/**
	 * Returns the Twitter username
	 *
	 * @return string
	 */
	public function getTwitterUsername() {
		return $this->twitterUsername;
	}

	/**
	 * Sets the Twitter username
	 *
	 * @param string $twitterUsername
	 */
	public function setTwitterUsername($twitterUsername) {
		$this->twitterUsername = $twitterUsername;
	}

	/**
	 * Adds a post to this blog
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post
	 * @return void
	 */
	public function addPost(\RobertLemke\Plugin\Blog\Domain\Model\Post $post) {
		$post->setBlog($this);
		$this->posts->add($post);
	}

	/**
	 * Removes a post from this blog
	 *
	 * @param \RobertLemke\Plugin\Blog\Domain\Model\Post $post
	 * @return void
	 */
	public function removePost(\RobertLemke\Plugin\Blog\Domain\Model\Post $post) {
		$this->posts->removeElement($post);
	}

	/**
	 * Returns all posts in this blog
	 *
	 * @return \Doctrine\Common\Collections\Collection<\RobertLemke\Plugin\Blog\Domain\Model\Post> The posts of this blog
	 */
	public function getPosts() {
		return $this->posts;
	}

	/**
	 * Sets the full description text
	 *
	 * @param string $fullDescription
	 * @return void
	 */
	public function setFullDescription($fullDescription) {
		$this->fullDescription = $fullDescription;
	}

	/**
	 * Returns the full description
	 *
	 * @return string
	 */
	public function getFullDescription() {
		return $this->fullDescription;
	}

	/**
	 * Sets the Google Analytics account number
	 *
	 * @param string $googleAnalyticsAccountNumber
	 * @return void
	 */
	public function setGoogleAnalyticsAccountNumber($googleAnalyticsAccountNumber) {
		$this->googleAnalyticsAccountNumber = $googleAnalyticsAccountNumber;
	}

	/**
	 * Returns the Google Analytics account number
	 *
	 * @return string
	 */
	public function getGoogleAnalyticsAccountNumber() {
		return $this->googleAnalyticsAccountNumber;
	}

	/**
	 * Set the keywords for this blog
	 *
	 * @param string $keywords
	 * @return void
	 */
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
	 * Returns the keywords of this blog
	 *
	 * @return string
	 */
	public function getKeywords() {
		return $this->keywords;
	}

}
?>