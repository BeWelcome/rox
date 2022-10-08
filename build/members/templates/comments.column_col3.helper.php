<?php
$comments = [];
$commentsReceived = $this->member->get_comments();
$commentsWritten = $this->member->get_comments_written();

foreach ($commentsReceived as $value) {
    $key = $value->UsernameFromMember;
    $comments[$key] = [
        'from' => $value,
    ];
}
foreach ($commentsWritten as $value) {
    $key = $value->UsernameToMember;
    if (isset($comments[$key])) {
        $comments[$key] = array_merge($comments[$key], [
            'to' => $value,
        ]);
    } else {
        $comments[$key] = [
            'to' => $value,
        ];
    }
}
$farFuture = new DateTimeImmutable('01-01-3000');
usort($comments,
    function ($a, $b) use ($farFuture) {
        // get latest updates on to and from part of comments and order desc
        $createdATo = isset($a['to']) ? new DateTime($a['to']->created) : $farFuture;
        $createdAFrom = isset($a['from']) ? new DateTime($a['from']->created) : $farFuture;
        $createdA = min($createdATo, $createdAFrom);
        $createdBTo = isset($b['to']) ? new DateTime($b['to']->created) : $farFuture;
        $createdBFrom = isset($b['from']) ? new DateTime($b['from']->created) : $farFuture;
        $createdB = min($createdBTo, $createdBFrom);
        return -1*($createdA <=> $createdB);
    }
);

$member = $this->member;
$username = $this->member->Username;
$layoutbits = new MOD_layoutbits();
