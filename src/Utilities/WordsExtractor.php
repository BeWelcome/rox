<?php

namespace App\Utilities;

use ArrayIterator;
use InvalidArgumentException;
use Iterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Extractor\PhpExtractor;
use Symfony\Component\Translation\Extractor\PhpStringTokenParser;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @SuppressWarnings(PHPMD)
 *
 * This is based on an example from the Symfony documentation.
 */
class WordsExtractor extends PhpExtractor
{
    /**
     * The sequence that captures translation messages.
     *
     * @var array
     */
    protected $sequences = [
        [
            '$words->',
            'get',
            '(',
            self::MESSAGE_TOKEN,
        ],
        [
            '->',
            'getSilent',
            '(',
            self::MESSAGE_TOKEN,
        ],
        [
            '->',
            'getFormatted',
            '(',
            self::MESSAGE_TOKEN,
        ],
        [
            '->',
            'ww',
            '(',
            self::MESSAGE_TOKEN,
        ],
    ];

    /**
     * Prefix for new found message.
     *
     * @var string
     */
    private $prefix = '';

    /**
     * {@inheritdoc}
     */
    public function extract($resource, MessageCatalogue $catalog)
    {
        $files = $this->extractFiles($resource);
        foreach ($files as $file) {
            $this->parseTokens(token_get_all(file_get_contents($file)), $catalog, $file);

            gc_mem_caches();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Normalizes a token.
     *
     * @param mixed $token
     *
     * @return string|null
     */
    protected function normalizeToken($token)
    {
        if (isset($token[1]) && 'b"' !== $token) {
            return $token[1];
        }

        return $token;
    }

    /**
     * Extracts trans message from PHP tokens.
     */
    protected function parseTokens(array $tokens, MessageCatalogue $catalog, string $filename)
    {
        $tokenIterator = new ArrayIterator($tokens);

        for ($key = 0; $key < $tokenIterator->count(); ++$key) {
            foreach ($this->sequences as $sequence) {
                $message = '';
                $domain = 'messages';
                $tokenIterator->seek($key);

                foreach ($sequence as $sequenceKey => $item) {
                    $this->seekToNextRelevantToken($tokenIterator);

                    if ($this->normalizeToken($tokenIterator->current()) === $item) {
                        $tokenIterator->next();
                        continue;
                    } elseif (self::MESSAGE_TOKEN === $item) {
                        $message = $this->getValue($tokenIterator);

                        if (\count($sequence) === ($sequenceKey + 1)) {
                            break;
                        }
                    } elseif (self::METHOD_ARGUMENTS_TOKEN === $item) {
                        $this->skipMethodArgument($tokenIterator);
                    } elseif (self::DOMAIN_TOKEN === $item) {
                        $domainToken = $this->getValue($tokenIterator);
                        if ('' !== $domainToken) {
                            $domain = $domainToken;
                        }

                        break;
                    } else {
                        break;
                    }
                }

                if ($message) {
                    $message = strtolower($message);
                    $catalog->set($message, $this->prefix . $message, $domain);
                    $metadata = $catalog->getMetadata($message, $domain) ?? [];
                    $normalizedFilename = preg_replace('{[\\\\/]+}', '/', $filename);
                    $metadata['sources'][] = $normalizedFilename . ':' . $tokens[$key][2];
                    $catalog->setMetadata($message, $metadata, $domain);
                    break;
                }
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    protected function canBeExtracted(string $file)
    {
        return $this->isFile($file) && 'php' === pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractFromDirectory($directory)
    {
        $finder = new Finder();

        return $finder->files()->name('*.php')->in($directory);
    }

    /**
     * Seeks to a non-whitespace token.
     */
    private function seekToNextRelevantToken(Iterator $tokenIterator)
    {
        for (; $tokenIterator->valid(); $tokenIterator->next()) {
            $token = $tokenIterator->current();
            if (T_WHITESPACE !== $token[0]) {
                break;
            }
        }
    }

    private function skipMethodArgument(Iterator $tokenIterator)
    {
        $openBraces = 0;

        for (; $tokenIterator->valid(); $tokenIterator->next()) {
            $token = $tokenIterator->current();

            if ('[' === $token[0] || '(' === $token[0]) {
                ++$openBraces;
            }

            if (']' === $token[0] || ')' === $token[0]) {
                --$openBraces;
            }

            if ((0 === $openBraces && ',' === $token[0]) || (-1 === $openBraces && ')' === $token[0])) {
                break;
            }
        }
    }

    /**
     * Extracts the message from the iterator while the tokens
     * match allowed message tokens.
     */
    private function getValue(Iterator $tokenIterator)
    {
        $message = '';
        $docToken = '';
        $docPart = '';

        for (; $tokenIterator->valid(); $tokenIterator->next()) {
            $token = $tokenIterator->current();
            if ('.' === $token) {
                // Concatenate with next token
                continue;
            }
            if (!isset($token[1])) {
                break;
            }

            switch ($token[0]) {
                case T_START_HEREDOC:
                    $docToken = $token[1];
                    break;
                case T_ENCAPSED_AND_WHITESPACE:
                case T_CONSTANT_ENCAPSED_STRING:
                    if ('' === $docToken) {
                        $message .= PhpStringTokenParser::parse($token[1]);
                    } else {
                        $docPart = $token[1];
                    }
                    break;
                case T_END_HEREDOC:
                    $message .= PhpStringTokenParser::parseDocString($docToken, $docPart);
                    $docToken = '';
                    $docPart = '';
                    break;
                case T_WHITESPACE:
                    break;
                default:
                    break 2;
            }
        }

        return $message;
    }
}
