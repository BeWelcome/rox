<?php

namespace App\Model;

use App\Entity\FeedbackCategory;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Address;

class AboutModel
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly Mailer $mailer)
    {
    }

    public function getFeedbackCategories()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('c')
            ->from(FeedbackCategory::class, 'c')
            ->where('c.visible = 1')
            ->orderBy('c.sortorder', 'ASC')
            ->indexBy('c', 'c.id')
        ;

        return $qb->getQuery()->getResult();
    }

    public function sendFeedbackEmail($data): void
    {
        /** @var FeedbackCategory $category */
        $category = $data['IdCategory'];
        $notifyEmail = new Address($category->getEmailtonotify());
        $feedbackEmail = $data['FeedbackEmail'];
        if (null === $feedbackEmail) {
            $feedbackEmail = 'feedback@bewelcome.org';
        }

        $this->mailer->sendFeedbackEmail($feedbackEmail, $notifyEmail, $data);
    }
}
