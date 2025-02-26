<?php
declare(strict_types=1);

namespace RobertLemke\Plugin\Blog\Eel\Helper;

use Neos\Eel\ProtectedContextAwareInterface;

class Gravatar implements ProtectedContextAwareInterface
{
    /**
     * @var array|string[]
     */
    protected array $allowedRatings = ['g', 'pg', 'r', 'x'];

    /**
     * @param string $email Gravatar Email
     * @param int $size Size in pixels, defaults to 64px [ 1 - 2048 ]
     * @param string $rating Maximum rating (inclusive) [ g | pg | r | x ]
     * @param string $default Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ] or a URL
     * @param bool $forceDefault Force default image to always load [ true | false ]
     * @return string
     */
    public function getGravatarURL(string $email, int $size = 64, string $rating = 'g', string $default = 'mp', bool $forceDefault = false): string
    {
        $sanitizedEmail = hash('sha256', strtolower(trim($email)));
        $gravatarUri = 'https://www.gravatar.com/avatar';

        $uriParts = [];

        if ($size > 0) {
            $uriParts['s'] = htmlentities((string)$size);
        }

        if (in_array($rating, $this->allowedRatings, true)) {
            $uriParts['r'] = htmlentities($rating);
        }

        if ($default !== '') {
            $uriParts['d'] = htmlentities($default);
        }

        if ($forceDefault === true) {
            $uriParts['f'] = 'y';
        }

        return sprintf('%s/%s?%s', $gravatarUri, $sanitizedEmail, http_build_query($uriParts));
    }

    /**
     * All methods are considered safe, i.e. can be executed from within Eel
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
