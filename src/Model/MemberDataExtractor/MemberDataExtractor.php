<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\CryptedField;
use App\Entity\Member;
use App\Entity\MemberTranslation;

final class MemberDataExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        return $this->writePersonalDataFile(
            [
                'member' => $member,
                'profilepicture' => 'images/empty_avatar.png',
            ],
            'profile'
        );

        // Write member information into file:
        $handle = fopen($tempDir . 'memberinfo.txt', 'w');
        fwrite($handle, json_encode($member));
        fwrite($handle, 'Username: ' . $member->getUsername() . PHP_EOL);
        fwrite($handle, 'Location: ' . $member->getCity()->getName() . PHP_EOL);
        fwrite($handle, 'Birthdate: ' . $member->getBirthdate() . PHP_EOL);
        fwrite($handle, 'Email address: ' . $member->getEmail() . PHP_EOL);
        fwrite($handle, 'Accommodation: ' . $member->getAccommodation() . PHP_EOL);

        $cryptedFields = $member->getCryptedFields();
        /** @var CryptedField $crypted */
        foreach ($cryptedFields as $crypted) {
            fwrite($handle, $crypted->getTablecolumn() . ':' . $crypted->getMemberCryptedValue() . PHP_EOL);
        }

        $memberFields = $member->getMemberFields();
        /** @var MemberTranslation $memberField */
        foreach ($memberFields as $memberField) {
            fwrite($handle, $memberField->getTablecolumn() . ' (' . $memberField->getLanguage()->getName() . '): ' . $memberField->getSentence() . PHP_EOL);
        }
        fclose($handle);
    }
}
