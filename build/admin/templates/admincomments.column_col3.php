<?php
$total_bad_comments = count($this->bad_comments);
$words = $this->getWords();
echo <<<HTML
<p>
Hi {$this->member->Username}.<br/>
</p>
<p>Displaying comments marked problematic: 

{$this->pager->render()}
HTML;

foreach ($this->pager->getActiveSubset($this->bad_comments) as $comment)
{
    $from = ($member = $comment->getFromMember()) ? $member->Username : '';
    $to = ($member = $comment->getToMember()) ? $member->Username : '';
    echo <<<HTML
<div>
    <table style='width: 100%; border: 1px solid black'>
        <tbody>
            <tr>
                <td>To: <b><a href="members/{$from}">{$from}</a></b></td>
                <td>From: <b><a href="members/{$to}">{$to}</a></b></td>
            </tr>
            <tr>
                <td>Experience: <b>{$comment->Quality}</b></td>
                <td>Meeting type: <b>{$comment->Lenght}</b></td>
            </tr>
            <tr>
                <td>Created: <b>{$comment->created}</b></td>
                <td>Updated: <b>{$comment->updated}</b></td>
            </tr>
            <tr>
                <td colspan='2'>Comment mark: <b>{$comment->AdminAction}</b></td>
            </tr>
            <tr>
                <td colspan='2'><i>Meeting place</i>: {$comment->TextWhere}</td>
            </tr>
            <tr>
                <td colspan='2'><i>Comment text</i>: {$comment->TextFree}</td>
            </tr>
        </tbody>
    </table>
</div>
HTML;
}
