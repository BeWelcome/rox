<?php
        $member = $this->member;
        $comments_received = $this->member->comments;
        $comments_written = $this->member->get_comments_written();

        $comments_from = array();
        $comment_for_self = false;
        // Get member ids into one array
        $member_ids = array();
        foreach($comments_received as $c) {
            if (!in_array($c->IdFromMember, $member_ids)) {
                $member_ids[] = $c->IdFromMember;
                $comments_from[$c->IdFromMember] = $c;
            }
        }

        $comments_to = array();
        foreach($comments_written as $c) {
            if (!in_array($c->IdToMember, $member_ids)) {
                $member_ids[] = $c->IdToMember;
            }
            $comments_to[$c->IdToMember] = $c;
        }

        // check if member_ids contains a comment for the member browsing the
        // the profile
        $comment_to_self_exists = false;
        $visitor = $this->model->getLoggedInMember();
        if ($visitor && ($visitor->id <> $member->id) && (in_array($visitor->id, $member_ids))) {
            $comment_to_self_exists = true;
        }

        // now that we have all members we create one array with all comments
        // containing "timestamp", "from", "to" as fields
        $i = 0;
        $comments = array();
        foreach($member_ids as $id) {
            $comment = array();
            $comment_to_self = false;
            if ($visitor && ($visitor->id == $id)) {
                $comment_to_self = true;
            }

            $ts_from = $ts_to = 0;
            if (isset($comments_from[$id])) {
                $comment['from'] = $comments_from[$id];
                $ts_from = $comments_from[$id]->unix_created;
            }
            if (isset($comments_to[$id])) {
                $comment['to'] = $comments_to[$id];
                $ts_to = $comments_to[$id]->unix_created;
            }

            // Add comments to list if it isn't for the current visitor
            // or if the visitor left a comment him-/herself
            if (!$comment_to_self || isset($comment['from'])) {
                $comment['timestamp'] = min($ts_from, $ts_to);
                $comments[] = $comment;
            }
        }

        // sort the comments descending by timestamp
        function cmp($a, $b)
        {
            if ($a['timestamp'] == $b['timestamp']) {
                return 0;
            }
            return ($a['timestamp'] > $b['timestamp']) ? -1 : 1;
        }

        usort($comments, "cmp");

        // if current visitor didn't leave a comment add entry to the beginning
        // of the comment list

        if ($comment_to_self_exists && (!isset($comments_from[$visitor->id]))) {
            $comment = array();
            $comment['to'] = $comments_to[$visitor->id];
            array_unshift($comments, $comment);
        }

        $username = $this->member->Username;
        $layoutbits = new MOD_layoutbits();
?>
