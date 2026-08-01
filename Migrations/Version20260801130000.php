<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260801130000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Convert columns backed by App\Doctrine\EnumType from native ENUM to VARCHAR, '
            . 'since Doctrine DBAL cannot introspect a bare ENUM without a stored DC2Type comment hint, '
            . 'which broke doctrine:migrations:diff.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment MODIFY COLUMN admin_action VARCHAR(21) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE comment MODIFY COLUMN quality VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE comments MODIFY COLUMN AdminAction VARCHAR(21) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE comments MODIFY COLUMN Quality VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_post MODIFY COLUMN OwnerCanStillEdit VARCHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_post MODIFY COLUMN PostDeleted VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_post MODIFY COLUMN PostVisibility VARCHAR(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_thread MODIFY COLUMN ThreadDeleted VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_thread MODIFY COLUMN ThreadVisibility VARCHAR(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_thread MODIFY COLUMN WhoCanReply VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE groups MODIFY COLUMN Type VARCHAR(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE member MODIFY COLUMN Accommodation VARCHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE member MODIFY COLUMN Gender VARCHAR(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE member MODIFY COLUMN Status VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE membersgroups MODIFY COLUMN Status VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE members_threads_subscribed MODIFY COLUMN ActionToWatch VARCHAR(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE member_language_level MODIFY COLUMN level VARCHAR(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE messages MODIFY COLUMN InFolder VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE messages MODIFY COLUMN Status VARCHAR(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE reports_to_moderators MODIFY COLUMN LastWhoSpoke VARCHAR(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE reports_to_moderators MODIFY COLUMN Status VARCHAR(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE reports_to_moderators MODIFY COLUMN Type VARCHAR(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE trips MODIFY COLUMN additionalInfo VARCHAR(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE word MODIFY COLUMN domain VARCHAR(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE word MODIFY COLUMN donottranslate VARCHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment MODIFY COLUMN admin_action ENUM(\'NothingNeeded\', \'AdminCommentMustCheck\', \'AdminAbuserMustCheck\', \'Checked\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE comment MODIFY COLUMN quality ENUM(\'positive\', \'neutral\', \'negative\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE comments MODIFY COLUMN AdminAction ENUM(\'NothingNeeded\', \'AdminCommentMustCheck\', \'AdminAbuserMustCheck\', \'Checked\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE comments MODIFY COLUMN Quality ENUM(\'positive\', \'neutral\', \'negative\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_post MODIFY COLUMN OwnerCanStillEdit ENUM(\'Yes\', \'No\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_post MODIFY COLUMN PostDeleted ENUM(\'NotDeleted\', \'Deleted\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_post MODIFY COLUMN PostVisibility ENUM(\'NoRestriction\', \'MembersOnly\', \'GroupOnly\', \'Moderator\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_thread MODIFY COLUMN ThreadDeleted ENUM(\'NotDeleted\', \'Deleted\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_thread MODIFY COLUMN ThreadVisibility ENUM(\'NoRestriction\', \'MembersOnly\', \'GroupOnly\', \'Moderator\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE forum_thread MODIFY COLUMN WhoCanReply ENUM(\'MembersOnly\', \'GroupMembersOnly\', \'Moderators\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE groups MODIFY COLUMN Type ENUM(\'Public\', \'NeedAcceptance\', \'NeedInvitation\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE member MODIFY COLUMN Accommodation ENUM(\'yes\', \'no\', \'\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE member MODIFY COLUMN Gender ENUM(\'male\', \'female\', \'other\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE member MODIFY COLUMN Status ENUM(\'MailToConfirm\', \'Pending\', \'DuplicateSigned\', \'NeedMore\', \'Rejected\', \'CompletedPending\', \'Active\', \'TakenOut\', \'Banned\', \'Sleeper\', \'ChoiceInactive\', \'OutOfRemind\', \'Renamed\', \'ActiveHidden\', \'SuspendedBeta\', \'AskToLeave\', \'StopBoringMe\', \'PassedAway\', \'Buggy\', \'Activated\', \'MailConfirmed\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE membersgroups MODIFY COLUMN Status ENUM(\'In\', \'WantToBeIn\', \'Kicked\', \'Invited\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE members_threads_subscribed MODIFY COLUMN ActionToWatch ENUM(\'replies\', \'updates\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE member_language_level MODIFY COLUMN level ENUM(\'mother.tongue\', \'expert\', \'fluent\', \'intermediate\', \'beginner\', \'hello.only\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE messages MODIFY COLUMN InFolder ENUM(\'Normal\', \'junk\', \'Spam\', \'Draft\', \'requests\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE messages MODIFY COLUMN Status ENUM(\'Draft\', \'ToCheck\', \'Checked\', \'ToSend\', \'Sent\', \'Freeze\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE reports_to_moderators MODIFY COLUMN LastWhoSpoke ENUM(\'Member\', \'Moderator\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE reports_to_moderators MODIFY COLUMN Status ENUM(\'Open\', \'OnDiscussion\', \'Closed\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE reports_to_moderators MODIFY COLUMN Type ENUM(\'SeeText\', \'AllowMeToEdit\', \'Insults\', \'RemoveMyPost\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE trips MODIFY COLUMN additionalInfo ENUM(\'none\', \'single\', \'couple\', \'friends_mixed\', \'friends_same\', \'family\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE word MODIFY COLUMN domain ENUM(\'messages\', \'messages+intl-icu\', \'validators\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
        $this->addSql('ALTER TABLE word MODIFY COLUMN donottranslate ENUM(\'yes\', \'no\') CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL');
    }
}
