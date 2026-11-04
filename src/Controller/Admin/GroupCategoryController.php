<?php

namespace App\Controller\Admin;

use App\Entity\Group;
use App\Entity\GroupCategory;
use App\Entity\Member;
use App\Form\Admin\GroupCategoryNameType;
use App\Logger\Logger;
use App\Repository\GroupCategoryRepository;
use App\Repository\GroupRepository;
use App\Utilities\GroupAdminSubmenuTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The group categories, kept by the group admin team: the list (with a form to add one by its English name) and a
 * category's page (its names per language, its icon, the keywords that suggest it and the groups in it).
 * Their changes are logged.
 */
#[IsGranted(Member::ROLE_ADMIN_GROUP)]
class GroupCategoryController extends AbstractController
{
    use GroupAdminSubmenuTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /** The groups shown on a category's page before "Show all". */
    private const int GROUPS_SHOWN = 20;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Logger $logger,
    ) {
    }

    #[Route(path: '/admin/groups/categories', name: 'admin_groups_categories')]
    public function list(Request $request): Response
    {
        $form = $this->createForm(GroupCategoryNameType::class, null, ['new' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = new GroupCategory()->setName('en', $form->get('name')->getData());
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addTranslatedFlash('notice', 'admin.group.category.saved');
            $this->logger->write("Group category #{$category->getId()} ({$category->getName()}) added", 'Group');

            return $this->redirectToRoute('admin_groups_category', ['id' => $category->getId()]);
        }

        $categories = $this->getCategoryRepository()->findAllSortedByName($request->getLocale());

        return $this->render('admin/groups/categories.html.twig', [
            'categories' => $categories,
            'groupCounts' => $this->getCategoryRepository()->countGroups(),
            'form' => $form,
            'submenu' => ['items' => $this->getSubMenuItems(), 'active' => 'categories', 'counts' => ['categories' => \count($categories)]],
        ]);
    }

    /**
     * A category's names (one per language; an empty name removes the language, but not English), its icon, its
     * keywords and its groups.
     *
     * @param list<string> $locales
     * @param list<string> $icons
     */
    #[Route(path: '/admin/groups/categories/{id}', name: 'admin_groups_category', requirements: ['id' => '\d+'])]
    public function show(
        Request $request,
        GroupCategory $category,
        #[Autowire('%locales%')]
        array $locales,
        #[Autowire('%group_category_icons%')]
        array $icons,
    ): Response {
        $form = $this->createForm(GroupCategoryNameType::class, ['locale' => $request->query->getString('locale', $request->getLocale())], [
            'locales' => $locales,
            'display_locale' => $request->getLocale(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $locale = $form->get('locale')->getData();
            $name = (string) $form->get('name')->getData();
            if ('en' === $locale && '' === trim($name)) {
                $form->get('name')->addError(new FormError($this->getTranslator()->trans('admin.group.category.english')));
            } else {
                $category->setName($locale, $name);
                $this->entityManager->flush();

                $this->addTranslatedFlash('notice', 'admin.group.category.saved');
                $this->logger->write("Group category #{$category->getId()} ({$category->getName()}): name in {$locale} set", 'Group');

                return $this->redirectToRoute('admin_groups_category', ['id' => $category->getId()]);
            }
        }

        $groups = $this->getGroupRepository()->findInCategory($category);
        $all = $request->query->getBoolean('all');

        return $this->render('admin/groups/category.html.twig', [
            'category' => $category,
            'form' => $form,
            'icons' => $icons,
            'groups' => $all ? $groups : \array_slice($groups, 0, self::GROUPS_SHOWN),
            'groupCount' => \count($groups),
            'memberCounts' => $this->getGroupRepository()->countMembersOfGroups($groups),
            'candidates' => $this->getGroupRepository()->findApprovedNotInCategory($category),
            'submenu' => ['items' => $this->getSubMenuItems(), 'active' => 'categories'],
        ]);
    }

    /**
     * @param list<string> $icons
     */
    #[Route(path: '/admin/groups/categories/{id}/icon', name: 'admin_groups_category_icon', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function setIcon(
        Request $request,
        GroupCategory $category,
        #[Autowire('%group_category_icons%')]
        array $icons,
    ): RedirectResponse {
        $this->checkToken($request, $category);
        $icon = $request->request->getString('icon');
        if (!\in_array($icon, $icons, true)) {
            throw $this->createNotFoundException('Unknown icon.');
        }

        $category->setIcon($icon);
        $this->entityManager->flush();
        $this->logger->write("Group category #{$category->getId()} ({$category->getName()}): icon set to {$icon}", 'Group');

        return $this->redirectToRoute('admin_groups_category', ['id' => $category->getId()]);
    }

    /**
     * Adds the words typed (separated by commas) and removes the one whose × was pressed.
     */
    #[Route(path: '/admin/groups/categories/{id}/keywords', name: 'admin_groups_category_keywords', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function setKeywords(Request $request, GroupCategory $category): RedirectResponse
    {
        $this->checkToken($request, $category);
        $remove = mb_strtolower(trim($request->request->getString('remove')));
        $keywords = array_filter($category->getKeywords(), static fn (string $keyword): bool => $keyword !== $remove);
        $added = array_map('trim', explode(',', $request->request->getString('add')));
        $keywords = array_merge($keywords, array_filter($added, static fn (string $keyword): bool => '' !== $keyword && mb_strlen($keyword) <= 40));

        $before = $category->getKeywords();
        $category->setKeywords($keywords);
        if ($category->getKeywords() !== $before) {
            $this->entityManager->flush();
            $this->logger->write("Group category #{$category->getId()} ({$category->getName()}): keywords changed", 'Group');
        }

        return $this->redirectToRoute('admin_groups_category', ['id' => $category->getId(), '_fragment' => 'keywords']);
    }

    #[Route(path: '/admin/groups/categories/{id}/groups', name: 'admin_groups_category_add_group', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function addGroup(Request $request, GroupCategory $category): RedirectResponse
    {
        $this->checkToken($request, $category);
        $group = $this->entityManager->find(Group::class, $request->request->getInt('group'));
        if (null === $group || Group::APPROVED !== $group->getApproved()) {
            throw $this->createNotFoundException('No such group.');
        }

        if ($group->hasCategory($category)) {
            return $this->redirectToRoute('admin_groups_category', ['id' => $category->getId(), '_fragment' => 'groups']);
        }
        if (!$group->canTakeCategory()) {
            $this->addTranslatedFlash('error', 'group.categories.too.many');

            return $this->redirectToRoute('admin_groups_category', ['id' => $category->getId(), '_fragment' => 'groups']);
        }

        $group->addCategory($category);
        $this->entityManager->flush();
        $this->logger->write("Group {$this->groupLink($group)} added to category {$category->getName()} by {$this->getMember()->getUsername()}", 'Group');

        return $this->redirectToRoute('admin_groups_category', ['id' => $category->getId(), '_fragment' => 'groups']);
    }

    #[Route(path: '/admin/groups/categories/{id}/groups/{groupId}/remove', name: 'admin_groups_category_remove_group', requirements: ['id' => '\d+', 'groupId' => '\d+'], methods: ['POST'])]
    public function removeGroup(
        Request $request,
        GroupCategory $category,
        #[MapEntity(id: 'groupId')] Group $group,
    ): RedirectResponse {
        $this->checkToken($request, $category);
        if ($group->hasCategory($category)) {
            $group->removeCategory($category);
            $this->entityManager->flush();
            $this->logger->write("Group {$this->groupLink($group)} removed from category {$category->getName()} by {$this->getMember()->getUsername()}", 'Group');
        }

        return $this->redirectToRoute('admin_groups_category', ['id' => $category->getId(), '_fragment' => 'groups']);
    }

    /**
     * Deletes a group category; the groups in it just lose it.
     */
    #[Route(path: '/admin/groups/categories/{id}/delete', name: 'admin_groups_category_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, GroupCategory $category): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete-group-category-' . $category->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $this->logger->write("Group category #{$category->getId()} ({$category->getName()}) deleted", 'Group');
        $this->entityManager->remove($category);
        $this->entityManager->flush();
        $this->addTranslatedFlash('notice', 'admin.group.category.deleted');

        return $this->redirectToRoute('admin_groups_categories');
    }

    /**
     * The forms of a category's page (icon, keywords, groups) post this token.
     */
    private function checkToken(Request $request, GroupCategory $category): void
    {
        if (!$this->isCsrfTokenValid('group-category-' . $category->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }

    private function groupLink(Group $group): string
    {
        return '<a href="' . $this->generateUrl('group_start', ['group_id' => $group->getId()]) . '">'
            . htmlspecialchars($group->getName()) . '</a>';
    }

    private function getMember(): Member
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $member;
    }

    private function getCategoryRepository(): GroupCategoryRepository
    {
        /** @var GroupCategoryRepository $repository */
        $repository = $this->entityManager->getRepository(GroupCategory::class);

        return $repository;
    }

    private function getGroupRepository(): GroupRepository
    {
        /** @var GroupRepository $repository */
        $repository = $this->entityManager->getRepository(Group::class);

        return $repository;
    }
}
