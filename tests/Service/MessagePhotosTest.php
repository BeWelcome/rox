<?php

namespace App\Tests\Service;

use App\Service\MessagePhotos;
use PHPUnit\Framework\TestCase;

/**
 * A message's uploaded photos come apart from its text; other pictures stay in the text.
 */
class MessagePhotosTest extends TestCase
{
    public function testThePhotosComeApartFromTheText(): void
    {
        $parts = new MessagePhotos()->split(
            '<p>Here we are</p><p><img src="https://www.bewelcome.org/gallery/show/uploaded/12/abc" alt="" /></p>'
            . '<p><img src="/gallery/show/uploaded/13/def&amp;x" alt=""></p>'
        );

        self::assertSame(['https://www.bewelcome.org/gallery/show/uploaded/12/abc', '/gallery/show/uploaded/13/def&x'], $parts['photos']);
        self::assertSame('<p>Here we are</p>', $parts['text']);
    }

    public function testAMessageWithoutPhotosStaysAsItIs(): void
    {
        $html = '<p>Hello</p><p><img src="https://example.org/cat.jpg" alt=""></p>';

        self::assertSame(['photos' => [], 'text' => $html], new MessagePhotos()->split($html));
    }

    public function testOnlyPhotosLeaveNoText(): void
    {
        $parts = new MessagePhotos()->split('<p><img src="/gallery/show/uploaded/1/a" alt=""></p>');

        self::assertSame('', $parts['text']);
    }
}
