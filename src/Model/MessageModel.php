<?php

namespace App\Model;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Repository\MessageRepository;
use App\Service\Mailer;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Pagerfanta\Pagerfanta;
use PDO;

/**
 * Class MessageModel.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * Hide logic in DeleteRequestType
 */
class MessageModel
{
}
