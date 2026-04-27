<?php

namespace App\Model\MemberDataExtractor;

use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

abstract class AbstractExtractor
{
    protected $entrypointLookup;
    protected $environment;
    protected $registry;

    public function __construct(EntrypointLookupInterface $entrypointLookup, Environment $environment, ManagerRegistry $registry)
    {
        $this->entrypointLookup = $entrypointLookup;
        $this->environment = $environment;
        $this->registry = $registry;
    }

    protected function getRepository(string $className): ObjectRepository
    {
        return $this->registry->getManagerForClass($className)->getRepository($className);
    }

    protected function writePersonalDataFile(array $parameters, string $template, ?string $filename = null): string
    {
        $this->writeRenderedTemplate(
            $filename ?: $template,
            $template,
            $parameters
        );

        return $template;
    }

    protected function writeRenderedTemplate($filename, $template, $parameters): void
    {
        $this->entrypointLookup->reset();
        $parameters = array_merge($parameters, ['date_generated' => new DateTime()]);

        $handle = fopen($filename, 'w');
        fwrite($handle, (string) $this->environment->render(\sprintf('private/%s.html.twig', $template), $parameters));
        fclose($handle);
    }

    /**
     * @param array  $parameters
     * @param string $template     Template (without .html.twig) to be used (located in private/)
     * @param string $subDirectory Subdirectory name (no trailing /)
     * @param string $filename     File to be written (.html is added)
     */
    protected function writePersonalDataFileSubDirectory($parameters, $template, $subDirectory, $filename = null): void
    {
        if (!is_dir($subDirectory)) {
            mkdir($subDirectory);
        }

        $filename ??= $template;

        $parameters = array_merge($parameters, [
            'isSubDir' => true,
        ]);

        $this->writeRenderedTemplate(
            $subDirectory . '/' . $filename,
            $template,
            $parameters
        );
    }

    protected function imageExtension(string $filename): string
    {
        return match (mime_content_type($filename)) {
            'image/png' => '.png',
            'image/jpeg' => '.jpg',
            'image/gif' => '.gif',
            'image/bmp' => '.bmp',
            default => '',
        };
    }
}
