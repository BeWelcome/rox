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

    protected function writePersonalDataFile(array $parameters, string $template, string $filename = null): string
    {
        $this->writeRenderedTemplate(
            $filename ?: $template,
            $template,
            $parameters
        );

        return $template;
    }

    protected function writeRenderedTemplate($filename, $template, $parameters)
    {
        $this->entrypointLookup->reset();
        $parameters = array_merge($parameters, ['date_generated' => new DateTime()]);

        $handle = fopen($filename, 'w');
        fwrite($handle, $this->environment->render(sprintf('private/%s.html.twig', $template), $parameters));
        fclose($handle);
    }

    /**
     * @param array  $parameters
     * @param string $template     Template (without .html.twig) to be used (located in private/)
     * @param string $subDirectory Subdirectory name (no trailing /)
     * @param string $filename     File to be written (.html is added)
     */
    protected function writePersonalDataFileSubDirectory($parameters, $template, $subDirectory, $filename = null)
    {
        if (!is_dir($subDirectory)) {
            @mkdir($subDirectory);
        }

        $filename = (null === $filename) ? $template : $filename;

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
        switch (mime_content_type($filename)) {
            case 'image/png':
                return '.png';
            case 'image/jpeg':
                return '.jpg';
            case 'image/gif':
                return '.gif';
            case 'image/bmp':
                return '.bmp';
            default:
                return '';
        }
    }
}
