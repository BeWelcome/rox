<?php


class DatabaseSummaryModel extends RoxModelBase
{
    function getTablesByDatabase() {
        // TODO: This is a mock function, we need to implement the real one!
        return array(
            'chat_db' => array(
                'messages' => array('id', 'chatroom_id', 'text', 'author_id'),
                'chatrooms' => array('id', 'title', 'description'),
            ),
            'members_db' => array(
                'members' => array('id', 'username', 'address', 'aboutme'),
                'members_friends' => array('id', 'member_id', 'friend_id', 'friendship_description'),
            ),
            'locations_db' => array(
                'locations' => array('id', 'coordinates', 'name', 'description'),
                'distances' => array('id', 'here_id', 'there_id', 'distance'),
            ),
        );
    }
}


?>