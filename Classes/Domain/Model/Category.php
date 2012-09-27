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
 * A blog post category
 *
 * @FLOW3\Entity
 */
class Category {

	/**
	 * The category name
	 *
	 * @var string
	 * @FLOW3\Identity
	 * @FLOW3\Validate(type="Text")
	 * @FLOW3\Validate(type="StringLength", options={ "minimum"=1, "maximum"=80 })
	 * @ORM\Column(length=80)
	 */
	protected $name;

	/**
	 * Sets the category name
	 *
	 * @param string $name The category name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the category name
	 *
	 * @return string The category name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the category name
	 *
	 * @return string The category name
	 */
	public function __toString() {
		return $this->name;
	}
}
?>