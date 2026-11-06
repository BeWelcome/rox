<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;

/**
 * The member's conversations: one page per conversation in messages/ (all its messages the member still has), and a
 * list with one entry per conversation. Requests and invitations (RequestsExtractor, InvitationsExtractor) work the
 * same way, a conversation per request.
 */
class MessagesExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): array
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getRepository(Message::class);

        return $this->writeConversations(
            $member,
            $tempDir,
            'messages',
            $messageRepository->getMessagesSentBy($member),
            $messageRepository->getMessagesReceivedBy($member),
        );
    }

    /**
     * Writes a page per conversation and the list.
     *
     * @param string    $section  messages, requests or invitations (the list's page and the conversations' directory)
     * @param Message[] $sent
     * @param Message[] $received
     *
     * @return array<string, int> the section with its number of conversations, and the messages sent and received
     *                            (<section>_sent, <section>_received: the overview's numbers)
     */
    protected function writeConversations(Member $member, string $tempDir, string $section, array $sent, array $received): array
    {
        $single = substr($section, 0, -1);
        $conversations = [];
        foreach ($this->group(array_merge($sent, $received)) as $messages) {
            $first = $messages[0];
            $last = $messages[\count($messages) - 1];
            $file = $single . '-' . $first->getCreated()->toDateString() . '-' . $first->getId() . '.html';
            $conversation = [
                'file' => $section . '/' . $file,
                'messages' => $messages,
                'first' => $first,
                'last' => $last,
                'other' => $first->getSender() === $member ? $first->getReceiver() : $first->getSender(),
                'request' => $last->getRequest(),
            ];
            $this->writePersonalDataFileSubDirectory(['conversation' => $conversation, 'member' => $member], $single, $tempDir . $section, $file);
            $conversations[] = $conversation;
        }
        // the conversation answered last first
        usort($conversations, static fn (array $a, array $b) => $b['last']->getCreated() <=> $a['last']->getCreated());

        $this->writePersonalDataFile(
            [
                'conversations' => $conversations,
                $section . 'Sent' => \count($sent),
                $section . 'Received' => \count($received),
                'member' => $member,
            ],
            $section,
            $tempDir . $section . '.html'
        );

        return [
            $section => \count($conversations),
            $section . '_sent' => \count($sent),
            $section . '_received' => \count($received),
        ];
    }

    /**
     * The messages by conversation, oldest first: a request's messages by request, the others by subject or, without
     * one, by the first message they answer.
     *
     * @param Message[] $messages
     *
     * @return list<list<Message>>
     */
    private function group(array $messages): array
    {
        $groups = [];
        foreach ($messages as $message) {
            $groups[$this->conversationKey($message)][] = $message;
        }
        foreach ($groups as &$group) {
            usort($group, static fn (Message $a, Message $b) => [$a->getCreated(), $a->getId()] <=> [$b->getCreated(), $b->getId()]);
        }

        return array_values($groups);
    }

    private function conversationKey(Message $message): string
    {
        if (null !== $message->getRequest()) {
            return 'request-' . $message->getRequest()->getId();
        }
        if (null !== $message->getSubject()) {
            return 'subject-' . $message->getSubject()->getId();
        }

        $root = $message;
        for ($depth = 0; null !== $root->getParent() && $depth < 1000; ++$depth) {
            $root = $root->getParent();
        }

        return 'message-' . $root->getId();
    }
}
