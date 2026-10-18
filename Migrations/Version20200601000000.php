<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20200601000000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create the reshaped member/address/forum/translation tables (member, address, '
            . 'address_translations, member_translations, member_language_level, friend, comment, '
            . 'language, word, forum_thread, forum_post, member_photo) that the current entities map '
            . 'to. DDL only, matching Doctrine\'s own schema generation for these entities; data is '
            . 'copied from the legacy tables by the migration that follows this one.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS language (Name TINYTEXT NOT NULL, ShortCode VARCHAR(16) NOT NULL, IsWrittenLanguage TINYINT NOT NULL, IsSpokenLanguage TINYINT NOT NULL, IsSignLanguage TINYINT NOT NULL, PRIMARY KEY (ShortCode)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE IF NOT EXISTS member (id INT AUTO_INCREMENT NOT NULL, Username VARCHAR(32) NOT NULL, Email VARCHAR(255) NOT NULL, NewEmail VARCHAR(255) DEFAULT NULL, Locale VARCHAR(8) NOT NULL, LastActive DATETIME DEFAULT NULL, PassWord VARCHAR(100) DEFAULT NULL, Status VARCHAR(16) NOT NULL, Reminders INT NOT NULL, Name TEXT DEFAULT NULL, ShortName VARCHAR(255) DEFAULT NULL, HideAttribute INT NOT NULL, ProfileLanguage VARCHAR(8) DEFAULT NULL, Accommodation VARCHAR(3) DEFAULT NULL, AdditionalAccommodationInfo VARCHAR(255) DEFAULT NULL, ILiveWith VARCHAR(255) DEFAULT NULL, StandardOffers VARCHAR(17) DEFAULT NULL, MaxGuests INT NOT NULL, MaxLengthOfStay VARCHAR(255) DEFAULT NULL, Organizations VARCHAR(255) DEFAULT NULL, Restrictions VARCHAR(30) DEFAULT NULL, HouseRules VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, AboutMe VARCHAR(255) DEFAULT NULL, Occupation VARCHAR(255) DEFAULT NULL, Gender VARCHAR(6) NOT NULL, BirthDate DATE DEFAULT NULL, PastTrips VARCHAR(255) DEFAULT NULL, PlannedTrips VARCHAR(255) DEFAULT NULL, Hobbies VARCHAR(255) DEFAULT NULL, Books VARCHAR(255) DEFAULT NULL, Music VARCHAR(255) DEFAULT NULL, Movies VARCHAR(255) DEFAULT NULL, PleaseBring VARCHAR(255) DEFAULT NULL, WhereYouSleep VARCHAR(255) DEFAULT NULL, OfferGuests VARCHAR(255) DEFAULT NULL, OfferHosts VARCHAR(255) DEFAULT NULL, GettingThere VARCHAR(255) DEFAULT NULL, LastSwitchToActive DATETIME DEFAULT NULL, bewelcomed TINYINT NOT NULL, RegistrationKey VARCHAR(255) DEFAULT NULL, HostingInterest INT DEFAULT NULL, INDEX idx_member_status_lastactive (Status, LastActive), INDEX idx_member_accommodation (Accommodation), FULLTEXT INDEX ft_member_search (Username, Name, AboutMe, Hobbies, Books, Music, Movies, Occupation, PastTrips, PlannedTrips, PleaseBring, WhereYouSleep, OfferGuests, OfferHosts, GettingThere, Organizations, AdditionalAccommodationInfo, ILiveWith, MaxLengthOfStay, HouseRules), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE IF NOT EXISTS address (HouseNumber VARCHAR(255) DEFAULT NULL, StreetName VARCHAR(255) DEFAULT NULL, Zip VARCHAR(255) DEFAULT NULL, wheelChairAccessible TINYINT DEFAULT NULL, latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, active TINYINT DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, location INT DEFAULT NULL, INDEX IDX_D4E6F817597D3FE (member_id), INDEX IDX_D4E6F815E9E89CB (location), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE IF NOT EXISTS address_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, UNIQUE INDEX lookup_unique_idx (locale, object_id, field), INDEX IDX_CA160E9C232D562B (object_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE IF NOT EXISTS member_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, object_id INT DEFAULT NULL, FULLTEXT INDEX ft_member_translations_content (content), UNIQUE INDEX lookup_unique_idx (locale, object_id, field), INDEX IDX_B304440F232D562B (object_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE IF NOT EXISTS member_language_level (level VARCHAR(13) NOT NULL, member_id INT NOT NULL, language VARCHAR(16) NOT NULL, INDEX IDX_15F11F17597D3FE (member_id), INDEX IDX_15F11F1D4DB71B5 (language), PRIMARY KEY (member_id, language, level)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE IF NOT EXISTS friend (created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, left_confirmed TINYINT NOT NULL, right_confirmed TINYINT NOT NULL, left_id INT NOT NULL, right_id INT NOT NULL, INDEX IDX_55EEAC61E26CCE02 (left_id), INDEX IDX_55EEAC6154976835 (right_id), PRIMARY KEY (left_id, right_id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE IF NOT EXISTS comment (relations VARCHAR(87) NOT NULL, quality VARCHAR(8) NOT NULL, comment TEXT NOT NULL, updated DATETIME DEFAULT NULL, created DATETIME NOT NULL, admin_action VARCHAR(21) NOT NULL, show_to_other_members TINYINT NOT NULL, allow_edit TINYINT NOT NULL, id INT AUTO_INCREMENT NOT NULL, to_member_id INT DEFAULT NULL, from_member_id INT DEFAULT NULL, INDEX IDX_9474526C4434048F (to_member_id), INDEX IDX_9474526C650B4644 (from_member_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE IF NOT EXISTS word (code VARCHAR(128) NOT NULL, domain VARCHAR(17) NOT NULL, Sentence TEXT NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, majorupdate DATETIME DEFAULT NULL, donottranslate VARCHAR(3) NOT NULL, Description TEXT NOT NULL, TranslationPriority INT NOT NULL, isarchived TINYINT DEFAULT NULL, ShortCode VARCHAR(16) NOT NULL, author_id INT DEFAULT NULL, INDEX IDX_C3F175116FFD4460 (ShortCode), INDEX IDX_C3F17511F675F31B (author_id), PRIMARY KEY (code, ShortCode)) DEFAULT CHARACTER SET utf8mb4');

        // forum_thread and forum_post reference each other (first_postid/last_postid <-> threadid), so both
        // tables are created without those FKs first; every FK for this group is added afterwards instead.
        $this->addSql('CREATE TABLE IF NOT EXISTS forum_thread (id INT AUTO_INCREMENT NOT NULL, expiredate DATETIME DEFAULT NULL, IdTitle INT NOT NULL, title VARCHAR(255) NOT NULL, replies INT NOT NULL, views INT NOT NULL, stickyvalue INT NOT NULL, ThreadVisibility VARCHAR(13) NOT NULL, WhoCanReply VARCHAR(16) NOT NULL, ThreadDeleted VARCHAR(10) NOT NULL, first_postid INT NOT NULL, last_postid INT NOT NULL, ShortCode VARCHAR(16) NOT NULL, IdGroup INT DEFAULT NULL, UNIQUE INDEX UNIQ_298F7F5295E45CB7 (first_postid), UNIQUE INDEX UNIQ_298F7F52FF177475 (last_postid), INDEX IdGroup (IdGroup), INDEX ThreadVisibility (ThreadVisibility), INDEX ThreadDeleted (ThreadDeleted), INDEX IDX_298F7F526FFD4460 (ShortCode), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE IF NOT EXISTS forum_post (id INT AUTO_INCREMENT NOT NULL, PostVisibility VARCHAR(13) NOT NULL, create_time DATETIME NOT NULL, message TEXT NOT NULL, IdContent INT NOT NULL, OwnerCanStillEdit VARCHAR(3) NOT NULL, last_edittime DATETIME DEFAULT NULL, last_editorid INT DEFAULT NULL, edit_count TINYINT NOT NULL, PostDeleted VARCHAR(10) NOT NULL, threadid INT DEFAULT NULL, IdWriter INT DEFAULT NULL, ShortCode VARCHAR(16) NOT NULL, INDEX last_editorid (last_editorid), INDEX threadid (threadid), INDEX IdWriter (IdWriter), INDEX PostVisibility (PostVisibility), INDEX PostDeleted (PostDeleted), INDEX create_time (create_time), INDEX IDX_996BCC5A6FFD4460 (ShortCode), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE IF NOT EXISTS member_photo (FilePath TINYTEXT NOT NULL, created DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_769611EB7597D3FE (member_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        // forum_thread.IdGroup intentionally gets no FK constraint below: some existing forum_thread
        // rows reference group ids that no longer exist in `groups` (data, not schema), so adding the
        // constraint fails on real data. The column is still created so the ORM mapping works; cleaning
        // up the orphaned rows is a separate concern from this member-data migration.
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F817597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F815E9E89CB FOREIGN KEY (location) REFERENCES geo__names (geoname_id)');
        $this->addSql('ALTER TABLE address_translations ADD CONSTRAINT FK_CA160E9C232D562B FOREIGN KEY (object_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_translations ADD CONSTRAINT FK_B304440F232D562B FOREIGN KEY (object_id) REFERENCES member (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_language_level ADD CONSTRAINT FK_15F11F17597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE member_language_level ADD CONSTRAINT FK_15F11F1D4DB71B5 FOREIGN KEY (language) REFERENCES language (ShortCode)');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC61E26CCE02 FOREIGN KEY (left_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC6154976835 FOREIGN KEY (right_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4434048F FOREIGN KEY (to_member_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C650B4644 FOREIGN KEY (from_member_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE word ADD CONSTRAINT FK_C3F175116FFD4460 FOREIGN KEY (ShortCode) REFERENCES language (ShortCode)');
        $this->addSql('ALTER TABLE word ADD CONSTRAINT FK_C3F17511F675F31B FOREIGN KEY (author_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE forum_thread ADD CONSTRAINT FK_298F7F5295E45CB7 FOREIGN KEY (first_postid) REFERENCES forum_post (id)');
        $this->addSql('ALTER TABLE forum_thread ADD CONSTRAINT FK_298F7F52FF177475 FOREIGN KEY (last_postid) REFERENCES forum_post (id)');
        $this->addSql('ALTER TABLE forum_thread ADD CONSTRAINT FK_298F7F526FFD4460 FOREIGN KEY (ShortCode) REFERENCES language (ShortCode)');
        $this->addSql('ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5AD0FD9043 FOREIGN KEY (threadid) REFERENCES forum_thread (id)');
        $this->addSql('ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5ADC7124E FOREIGN KEY (IdWriter) REFERENCES member (id)');
        $this->addSql('ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5A6FFD4460 FOREIGN KEY (ShortCode) REFERENCES language (ShortCode)');
        $this->addSql('ALTER TABLE member_photo ADD CONSTRAINT FK_769611EB7597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // addSql() only queues statements for execution after down() returns, so the FK-check
        // toggles have to be queued the same way as the drops - calling $this->connection
        // directly here would run them immediately, before any queued DROP TABLE executes.
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('DROP TABLE IF EXISTS member_photo');
        $this->addSql('DROP TABLE IF EXISTS forum_post');
        $this->addSql('DROP TABLE IF EXISTS forum_thread');
        $this->addSql('DROP TABLE IF EXISTS word');
        $this->addSql('DROP TABLE IF EXISTS comment');
        $this->addSql('DROP TABLE IF EXISTS friend');
        $this->addSql('DROP TABLE IF EXISTS member_language_level');
        $this->addSql('DROP TABLE IF EXISTS member_translations');
        $this->addSql('DROP TABLE IF EXISTS address_translations');
        $this->addSql('DROP TABLE IF EXISTS address');
        $this->addSql('DROP TABLE IF EXISTS member');
        $this->addSql('DROP TABLE IF EXISTS language');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }
}
