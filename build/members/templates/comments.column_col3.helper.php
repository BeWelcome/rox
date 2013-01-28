<?php
        $member = $this->member;
        $comments_received = $this->member->comments;
        $comments_written = $this->member->get_comments_written();

        $comments_from = array();
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

        // now that we have all members we create one array with all comments
        // containing "timestamp", "from", "to" as fields
        $i = 0;
        $comments = array();
        foreach($member_ids as $id) {
            $comment = array();
            $ts_from = $ts_to = 0;
            if (isset($comments_from[$id])) {
                $comment['from'] = $comments_from[$id];
                $ts_from = $comments_from[$id]->unix_updated;
            }
            if (isset($comments_to[$id])) {
                $comment['to'] = $comments_to[$id];
                $ts_to = $comments_to[$id]->unix_updated;
            }
            $comment['timestamp'] = max($ts_from, $ts_to);
            $comments[] = $comment;
        }
        $username = $this->member->Username;
        $layoutbits = new MOD_layoutbits();
?>
