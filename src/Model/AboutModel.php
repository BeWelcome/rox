<?php

namespace App\Model;

use App\Entity\Feedback;
use App\Entity\FeedbackCategory;
use App\Entity\Language;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Address;

class AboutModel
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager, Mailer $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    public function getFeedbackCategories()
    {
        $qb = $this->entityManager->createQueryBuilder('c');
        $qb
            ->select('c')
            ->from(FeedbackCategory::class, 'c')
            ->where('c.visible = 1')
            ->orderBy('c.sortorder', 'ASC')
            ->indexBy('c', 'c.id')
        ;


        return $qb->getQuery()->getResult();
    }

    public function sendFeedbackEmail($data)
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

    public function addFeedback($data)
    {
        $feedback = new Feedback();
        if (null !== $data['member']) {
            $feedback->setAuthor($data['member']);
        }
        $feedback->setDiscussion($data['FeedbackQuestion']);
        $feedback->setCategory($data['IdCategory']);
        $languageRepository = $this->entityManager->getRepository(Language::class);
        $english = $languageRepository->find(0);
        $feedback->setLanguage($english);
        $this->entityManager->persist($feedback);
        $this->entityManager->flush();
    }
}
