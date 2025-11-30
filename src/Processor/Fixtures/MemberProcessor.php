<?php

namespace App\Processor\Fixtures;

use App\Entity\Member;
use App\Entity\MemberTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final readonly class MemberProcessor implements ProcessorInterface
{
    private const array LOCALES = [
        'fr', 'es', 'zh-hans', 'ua',
    ];

    private const array FIELDS = [
        'GenderOfGuests',
        'Occupation',
        'ILiveWith',
        'MaxLengthOfStay',
        'Organizations',
        'AdditionalAccommodationInfo',
        'OtherRestrictions',
        'InformationToGuest',
        'AboutMe',
        'Hobbies',
        'Books',
        'Music',
        'Movies',
        'PleaseBring',
        'OfferGuests',
        'OfferHosts',
        'PublicTransport',
        'PastTrips',
        'PlannedTrips',
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Generator $faker,
        private PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
    }

    public function preProcess(string $id, $object): void
    {
        if (!$object instanceof Member) {
            return;
        }

        // Make sure name is shown
        $hideAttribute = $object->getHideAttribute();
        $object->setHideAttribute(0);
        if (null === $object->getName()) {
            $name = $this->faker->firstName($object->getGender()) . ' ' . $this->faker->lastName($object->getGender());
            $shortName = null;

            $setShortName = rand(0, 100);
            if (25 <= $setShortName) {
                $space = strpos($name, ' ');
                $shortName = substr($name, 0, $space);
            }
            $object->setShortName($shortName);
            $object->setName($name);
        }
        $object->setHideAttribute($hideAttribute);

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($object);
        $hashedPassword = $passwordHasher->hash('password');
        $object->setPassword($hashedPassword);

        // set created date
        $joined = $this->faker->dateTimeBetween('-12years', 'now');
        $object->setCreated($joined);
        $lastActivity = $this->faker->dateTimeBetween($joined->format('c'), 'now');
        $object->setLastActive($lastActivity);
    }

    public function postProcess(string $id, $object): void
    {
        if (!$object instanceof Member) {
            return;
        }
        $this->assignRandomizedFields($object);

        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    private function assignRandomizedFields(Member &$object): void
    {
        $locales = $this->getRandomLocales();

        foreach ($locales as $locale) {
            $object->addTranslation(new MemberTranslation($locale, 'ProfileLanguage', $locale));
            $fields = $this->getRandomFields();
            foreach ($fields as $field) {
                $text = $this->faker->sentences(rand(1, 7), true);
                $object->addTranslation(new MemberTranslation($locale, $field, $text));
            }
        }
    }

    /**
     * Returns a string array with profile languages. Random selection of 5 languages but English is always included.
     */
    private function getRandomLocales(): array
    {
        $countOfLocales = $this->faker->biasedNumberBetween(0, \count(self::LOCALES));
        if (0 === $countOfLocales) {
            return ['en'];
        }

        if (1 !== $countOfLocales) {
            $randomLocales = array_intersect_key(self::LOCALES, array_flip(array_rand(self::LOCALES, $countOfLocales)));
        } else {
            $randomLocales = [self::LOCALES[array_rand(self::LOCALES, 1)]];
        }

        // Always include English
        return array_merge(['en'], $randomLocales);
    }

    /**
     * Returns a string array with profile fields to be added as translation.
     */
    private function getRandomFields(): array
    {
        $countOfFields = $this->faker->biasedNumberBetween(0, \count(self::FIELDS));
        if (0 === $countOfFields) {
            return [];
        }

        if (1 !== $countOfFields) {
            $randomFields = array_intersect_key(self::FIELDS, array_flip(array_rand(self::FIELDS, $countOfFields)));
        } else {
            $randomFields = [self::FIELDS[array_rand(self::FIELDS, 1)]];
        }

        // Always include English
        return $randomFields;
    }
}
