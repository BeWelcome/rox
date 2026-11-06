<?php

namespace App\Controller\Admin;

use App\Entity\FaqCategory;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * The FAQ admin's pages (templates/admin/faqs/): the categories with their number of FAQs on the side of every page.
 */
class FaqBaseController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    protected function denyUnlessFaqAdmin(): void
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }
    }

    /**
     * Renders an admin page with the categories for the side.
     *
     * @param array<string, mixed> $parameters
     * @param int|string           $active     the id of the open category, or sort or create
     */
    protected function renderAdmin(string $template, array $parameters, int|string $active): Response
    {
        $counts = [];
        foreach ($this->entityManager->getConnection()->fetchAllAssociative('SELECT idCategory, COUNT(*) AS n FROM faq GROUP BY idCategory') as $row) {
            $counts[(int) $row['idCategory']] = (int) $row['n'];
        }

        return $this->render($template, $parameters + [
            'categories' => $this->entityManager->getRepository(FaqCategory::class)->findBy([], ['sortOrder' => 'ASC']),
            'counts' => $counts,
            'active' => $active,
        ]);
    }

    /**
     * @return list<int> the ids of a sort order the page sends (comma-separated)
     */
    protected function sortedIds(string $order): array
    {
        return array_values(array_filter(array_map('intval', explode(',', $order)), static fn (int $id): bool => $id > 0));
    }
}
