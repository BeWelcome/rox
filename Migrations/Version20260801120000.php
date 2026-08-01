<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260801120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Convert columns backed by App\Doctrine\SetType from native SET to VARCHAR, '
            . 'since Doctrine DBAL cannot introspect SET columns, which broke doctrine:migrations:diff.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment MODIFY COLUMN relations VARCHAR(87) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE comments MODIFY COLUMN Relations VARCHAR(87) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE member MODIFY COLUMN StandardOffers VARCHAR(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE member MODIFY COLUMN Restrictions VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE messages MODIFY COLUMN DeleteRequest VARCHAR(57) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE messages MODIFY COLUMN SpamInfo VARCHAR(71) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE sub_trips MODIFY COLUMN options VARCHAR(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment MODIFY COLUMN relations SET(\'was_my_guest\', \'hosted_me\', \'only_once\', \'family\', \'close_friend\', \'travelled_Together\', \'friends\', \'chatted\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE comments MODIFY COLUMN Relations SET(\'was_my_guest\', \'hosted_me\', \'only_once\', \'family\', \'close_friend\', \'travelled_Together\', \'friends\', \'chatted\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE member MODIFY COLUMN StandardOffers SET(\'dinner\', \'guidedtour\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE member MODIFY COLUMN Restrictions SET(\'no.alcohol\', \'no.drugs\', \'no.smoking\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE messages MODIFY COLUMN DeleteRequest SET(\'senderdeleted\', \'receiverdeleted\', \'senderpurged\', \'receiverpurged\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE messages MODIFY COLUMN SpamInfo SET(\'NotSpam\', \'SpamBlkWord\', \'SpamSayMember\', \'SpamSayChecker\', \'ProcessedBySpamManager\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE sub_trips MODIFY COLUMN options SET(\'Private\', \'MeetLocals\', \'LookingForHosts\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL');
    }
}