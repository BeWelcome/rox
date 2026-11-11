<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\MemberPhoto;
use App\Model\MemberDataExtractor\ExtractorInterface;
use App\Model\MemberDataExtractor\MyDataSections;
use App\Model\MemberDataExtractor\ProfilePicture;
use App\Utilities\MemoryLimit;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;
use ZipArchive;

/**
 * The personal data export (/mydata): static HTML pages in a zip that work offline. Every extractor writes its pages
 * and reports its sections with their counts; then every page gets the sidebar with all sections (the layout
 * leaves a marker for it, private/mydata.html.twig), and the overview is written.
 */
class MemberModel
{
    use TranslatorTrait;

    /** The CSS of the export (assets/tailwindcss/mydata.css): fonts, icons and the site's components */
    private const string ENTRY = 'mydata';

    /** The layout's markers, replaced once every section is known */
    private const string SIDEBAR_MARKER = '~<!--mydata:sidebar:([a-z_]*)-->~';
    private const string ME_MARKER = '<!--mydata:me-->';

    private string $tempDir;

    public function __construct(
        private readonly Environment $environment,
        private readonly EntityManagerInterface $entityManager,
        private readonly EntrypointLookupInterface $entrypointLookup,
        private readonly ContainerBagInterface $params,
        private readonly iterable|ExtractorInterface $extractors,
    ) {
    }

    public function collectPersonalData(Member $member): string
    {
        $dirname = null;
        for ($i = 0; $i < 1000 && null === $dirname; ++$i) {
            $candidate = sys_get_temp_dir() . '/' . uniqid('mydata_', true);
            if (!file_exists($candidate) && mkdir($candidate)) {
                $dirname = $candidate;
            }
        }
        if (null === $dirname) {
            throw new Exception('Can\'t generate temp dir');
        }

        // ends with /; used all over the place
        $dirname .= '/';
        $this->tempDir = $dirname;
        $this->preparePersonalData($member, $dirname);

        $zipFilename = $dirname . 'bewelcome-' . $member->getUsername() . '-' . date('Y-m-d') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipFilename, ZipArchive::CREATE);

        $filesToDelete = [];
        foreach ($this->files($dirname) as $file) {
            $filePath = $file->getRealPath();
            $zip->addFile($filePath, substr((string) $filePath, \strlen($dirname)));
            $filesToDelete[] = $filePath;
        }

        // Zip archive will be created only after closing object
        $zip->close();

        // Cleanup as this is personal data
        foreach ($filesToDelete as $file) {
            unlink($file);
        }

        return $zipFilename;
    }

    private function preparePersonalData(Member $member, string $tempDir): void
    {
        MemoryLimit::atLeast('512M', fn () => $this->extractPersonalData($member, $tempDir));
    }

    private function extractPersonalData(Member $member, string $tempDir): void
    {
        $this->copyAssets($this->params->get('kernel.project_dir'));

        $counts = [];
        foreach ($this->extractors as $extractor) {
            $counts += $extractor->extract($member, $tempDir);
        }

        $photos = $this->entityManager->getRepository(MemberPhoto::class)->findBy(['member' => $member], ['created' => 'DESC']);
        $picture = ProfilePicture::files($photos);
        $this->writeRenderedTemplate('index', 'index', [
            'member' => $member,
            'picture' => $picture,
            'sections' => MyDataSections::withCounts($counts),
            'counts' => $counts,
        ]);
        $this->addSidebars($member, $picture, $counts);
    }

    /**
     * Puts the sidebar (the active section marked) and the member's chip of the top bar into every page; pages in a
     * subdirectory (messages/, polls/, …) link one level up.
     *
     * @param array{original: string, thumbnail: string}|null $picture
     * @param array<string, int>                              $counts
     */
    private function addSidebars(Member $member, ?array $picture, array $counts): void
    {
        $sections = MyDataSections::withCounts($counts);
        $rendered = [];
        foreach ($this->files($this->tempDir) as $file) {
            if ('html' !== $file->getExtension()) {
                continue;
            }
            $prefix = \dirname((string) $file->getRealPath()) . '/' === $this->tempDir ? '' : '../';
            $html = (string) file_get_contents($file->getRealPath());
            $html = preg_replace_callback(
                self::SIDEBAR_MARKER,
                function (array $match) use ($sections, $prefix, &$rendered): string {
                    return $rendered['sidebar' . $prefix . $match[1]] ??= $this->environment->render('private/_sidebar.html.twig', [
                        'sections' => $sections,
                        'active' => $match[1],
                        'prefix' => $prefix,
                    ]);
                },
                $html,
            );
            $html = str_replace(self::ME_MARKER, $rendered['me' . $prefix] ??= $this->environment->render('private/_me.html.twig', [
                'member' => $member,
                'picture' => $picture,
                'prefix' => $prefix,
            ]), $html);
            file_put_contents($file->getRealPath(), $html);
        }
    }

    private function writeRenderedTemplate(string $filename, string $template, array $parameters): void
    {
        $this->entrypointLookup->reset();
        $parameters = array_merge($parameters, ['date_generated' => new DateTime()]);

        file_put_contents(
            $this->tempDir . $filename . '.html',
            $this->environment->render('private/' . $template . '.html.twig', $parameters),
        );
    }

    /**
     * The export's CSS with the fonts, icons and images it uses, its url()s relative so that they work from the
     * unzipped folder; the logo and the avatar of other members.
     */
    private function copyAssets(string $projectDir): void
    {
        $filesystem = new Filesystem();
        $public = $projectDir . '/public';

        foreach ($this->entrypointLookup->getCssFiles(self::ENTRY) as $cssFile) {
            $cssDir = \dirname($cssFile);
            $css = preg_replace_callback(
                '~url\(\s*([\'"]?)(/build/[^\'")?#]+)([?#][^\'")]*)?\1\s*\)~',
                function (array $match) use ($filesystem, $public, $cssDir): string {
                    $asset = $match[2];
                    if (is_file($public . $asset)) {
                        $filesystem->copy($public . $asset, $this->tempDir . ltrim($asset, '/'));
                    }

                    return 'url(' . $match[1] . $this->relativePath($cssDir, $asset) . ($match[3] ?? '') . $match[1] . ')';
                },
                (string) file_get_contents($public . $cssFile),
            );
            $filesystem->dumpFile($this->tempDir . ltrim($cssFile, '/'), $css);
        }

        foreach (['images/logo_icon_2025.svg', 'images/logo_text_2025.svg', 'images/empty_avatar.png'] as $image) {
            $filesystem->copy($public . '/' . $image, $this->tempDir . $image);
        }
    }

    /** the path from a directory to a file, both from the web root (/build, /build/fonts/x.woff2 → fonts/x.woff2) */
    private function relativePath(string $fromDir, string $to): string
    {
        $from = array_values(array_filter(explode('/', $fromDir)));
        $target = array_values(array_filter(explode('/', $to)));
        while ([] !== $from && [] !== $target && $from[0] === $target[0]) {
            array_shift($from);
            array_shift($target);
        }

        return str_repeat('../', \count($from)) . implode('/', $target);
    }

    /**
     * @return iterable<SplFileInfo> the files (no directories) under the directory
     */
    private function files(string $directory): iterable
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file->isFile()) {
                yield $file;
            }
        }
    }
}
