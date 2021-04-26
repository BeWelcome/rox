<?php

namespace App\Model;

use App\Entity\Member;
use App\Model\MemberDataExtractor\ExtractorInterface;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Exception as Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use ZipArchive;

class MemberModel
{
    use ManagerTrait;
    use TranslatorTrait;

    /** @var EntrypointLookup */
    private $entrypointLookup;

    /** @var Environment */
    private $environment;

    /** @var string */
    private $tempDir;

    /** @var ContainerBagInterface */
    private $params;

    /** @var iterable|ExtractorInterface[] */
    private $extractors;

    public function __construct(
        Environment $environment,
        EntrypointLookupInterface $entrypointLookup,
        ContainerBagInterface $params,
        iterable $extractors
    ) {
        $this->environment = $environment;
        $this->entrypointLookup = $entrypointLookup;
        $this->params = $params;
        $this->extractors = $extractors;
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    public function collectPersonalData(Member $member)
    {
        // Create temp directory
        $i = 0;
        $dirname = '';
        while ($i < 1000) {
            $dirname = sys_get_temp_dir() . '/' . uniqid('mydata_', true);
            if (!is_file($dirname) && !is_dir($dirname)) {
                mkdir($dirname);
                break;
            }
        }

        // Ensure directory name ends with / and store it in private variable $tempDir as it is used all over the place
        // and clutters function signatures
        $dirname .= '/';
        $this->tempDir = $dirname;

        if (1000 === $i) {
            // 1000 tries to create a temp directory failed, oh my
            throw new Exception('Can\'t generate temp dir');
        }
        $this->preparePersonalData($member, $dirname);

        $zipFilename = $dirname . 'bewelcome-' . $member->getUsername() . '-' . date('Y-m-d') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipFilename, ZipArchive::CREATE);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $filesToDelete = [];
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, \strlen($dirname));

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
                $filesToDelete[] = $filePath;
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        // Cleanup as this is personal data
        foreach ($filesToDelete as $name => $file) {
            unlink($file);
        }

        return $zipFilename;
    }

    private function preparePersonalData(Member $member, string $tempDir)
    {
        $memoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        $extracted = [];
        $this->createStylesheetAndImageFolder($this->params->get('kernel.project_dir'));
        foreach ($this->extractors as $extractor) {
            $extracted[] = $extractor->extract($member, $tempDir);
        }
        $this->writePersonalDataFile(['member' => $member, 'extracted' => $extracted], 'index');

        ini_set('memory_limit', $memoryLimit);
    }

    /**
     * @param $filename
     * @param $template
     * @param $parameters
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function writeRenderedTemplate($filename, $template, $parameters)
    {
        $this->entrypointLookup->reset();
        $parameters = array_merge($parameters, ['date_generated' => new DateTime()]);

        $handle = fopen($this->tempDir . $filename . '.html', 'w');
        fwrite($handle, $this->environment->render('private/' . $template . '.html.twig', $parameters));
        fclose($handle);
    }

    /**
     * @param array  $parameters
     * @param string $template
     * @param string $filename
     */
    private function writePersonalDataFile($parameters, $template, $filename = null): string
    {
        $this->writeRenderedTemplate(
            $filename ?: $template,
            $template,
            $parameters
        );

        return $template;
    }

    private function createStylesheetAndImageFolder(string $projectDir)
    {
        $filesystem = new Filesystem();

        $cssFiles = $this->entrypointLookup->getCssFiles('bewelcome');
        foreach ($cssFiles as $cssFile) {
            $source = $projectDir . '/public' . $cssFile;
            $destination = $this->tempDir . $cssFile;
            $filesystem->copy($source, $destination);
        }

        $jsFiles = $this->entrypointLookup->getJavaScriptFiles('gallery');
        foreach ($jsFiles as $jsFile) {
            $source = $projectDir . '/public' . $jsFile;
            $destination = $this->tempDir . $jsFile;
            $filesystem->copy($source, $destination);
        }

        $jsFiles = $this->entrypointLookup->getJavaScriptFiles('bewelcome');
        foreach ($jsFiles as $jsFile) {
            $source = $projectDir . '/public' . $jsFile;
            $destination = $this->tempDir . $jsFile;
            $filesystem->copy($source, $destination);
        }

        // Add the Bewelcome logo
        $filesystem->copy($projectDir . '/public/images/logo_index_top.png', $this->tempDir . 'images/logo_index_top.png');

        // We also need to empty avatar image
        $filesystem->copy($projectDir . '/public/images/empty_avatar.png', $this->tempDir . 'images/empty_avatar.png');

        // The accommodation images
        $filesystem->copy($projectDir . '/public/images/icons/wheelchairblue.png', $this->tempDir . 'images/wheelchairblue.png');
        $filesystem->copy($projectDir . '/public/images/icons/anytime.png', $this->tempDir . 'images/anytime.png');
        $filesystem->copy($projectDir . '/public/images/icons/dependonrequest.png', $this->tempDir . 'images/dependonrequest.png');
        $filesystem->copy($projectDir . '/public/images/icons/neverask.png', $this->tempDir . 'images/neverask.png');
    }
}
