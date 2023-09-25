<?php

namespace App\Services;

use App\Models\Author;

class AuthorService
{
    /**
     * Create or retrieve an author based on the username.
     *
     * @param string $username
     * @return Author
     */
    protected function getOrCreateAuthor(string $username): Author
    {
        // Try to find an existing author with the provided username
        $author = Author::firstOrNew(['username' => $username], ['username' => $username]);
        if (!$author->exists) {
            // If the author is new, save the author record
            $author->save();
        }

        return $author;
    }

    /**
     * Get the author ID for the provided username.
     *
     * @param string $username
     * @return int
     */
    public function getAuthorId(string $username): int
    {
        $author = $this->getOrCreateAuthor($username);

        return $author->id;
    }
}
