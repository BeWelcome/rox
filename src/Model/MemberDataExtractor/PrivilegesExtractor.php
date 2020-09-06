<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Group;
use App\Entity\Member;
use App\Entity\PrivilegeScope;

final class PrivilegesExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        $privilegesCombined = [];
        $privilegesRepository = $this->getRepository(PrivilegeScope::class);
        $privileges = $privilegesRepository->findBy(['member' => $member]);
        if (!empty($privileges)) {
            /** @var PrivilegeScope $privilege */
            foreach ($privileges as $privilege) {
                $type = $privilege->getPrivilege()->getType();
                $scope = $privilege->getType();
                $realScope = $scope;
                $privilegeCombined = [];
                $privilegeCombined['privilege'] = $type;
                if ('Group' === $type) {
                    // Naming is a bit odd here
                    if (is_numeric($scope)) {
                        // Check if this group still exists
                        $groupRepository = $this->getRepository(Group::class);
                        /** @var Group $group */
                        $group = $groupRepository->findOneBy(['id' => $scope]);
                        if (null !== $group) {
                            $realScope = $group->getName();
                        } else {
                            $realScope = 'Deleted group (' . $scope . ')';
                        }
                    }
                }
                $privilegeCombined['scope'] = $realScope;
                $privilegeCombined['role'] = $privilege->getRole()->getName();
                $privilegeCombined['assigned'] = $privilege->getUpdated();
                $privilegesCombined[] = $privilegeCombined;
            }
        }

        return $this->writePersonalDataFile(['privileges' => $privilegesCombined], 'privileges', $tempDir . 'privileges.html');
    }
}
