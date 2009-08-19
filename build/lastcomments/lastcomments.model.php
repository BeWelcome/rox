<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.
*/


class LastcommentsModel extends  RoxModelBase
{

    public function __construct()
    {
        $this->BW_Right = MOD_right::get();
        parent::__construct();
    }


    /**
     * function GetLastComments Find and returns the twenty last comments
     * @$limit optional parameter define the limits of nb of comments
     */
    public function GetLastComments($limit=10) {
        $sql="select m1.Username as UsernameFrom,m2.Username as UsernameTo,comments.updated,UNIX_TIMESTAMP(comments.updated) unix_updated,TextWhere,TextFree,comments.Quality,
        country1.id as IdCountryFrom,city1.id as IdCityFrom,country1.Name as CountryNameFrom,
        country2.id as IdCountryTo,city2.id as IdCityTo,country2.Name as CountryNameTo,
        MemberNbComments(m2.id) as ToNbComment,MemberNbComments(m1.id) as FromNbComment,
        comments_ofthemomment_votes.IdComment as IdCommentHasVote,comments.id as IdComment
from (comments,members as m1,members as m2,cities as city1,countries as country1,cities as city2,countries as country2)
left join comments_ofthemomment_votes on comments_ofthemomment_votes.IdMember=".$_SESSION["IdMember"]." and  comments_ofthemomment_votes.IdComment=comments.id
where m1.id=IdFromMember and m2.id=IdToMember and m1.Status='Active' and m2.Status='Active' and DisplayableInCommentOfTheMonth='Yes'
and city1.id=m1.IdCity and country1.id=city1.IdCountry
and city2.id=m2.IdCity and country2.id=city2.IdCountry
order by comments.id desc limit $limit" ;


        $Data=$this->bulkLookup($sql) ;
        return($Data) ;
    }

    /**
     * function GetCommentOfTheMoment Find and returns the comment of the moment
     */
    public function GetCommentOfTheMoment() {
        $sql="select m1.Username as UsernameFrom,m2.Username as UsernameTo,comments.updated,UNIX_TIMESTAMP(comments.updated) unix_updated,TextWhere,TextFree,comments.Quality,
        country1.id as IdCountryFrom,city1.id as IdCityFrom,country1.Name as CountryNameFrom,
        country2.id as IdCountryTo,city2.id as IdCityTo,country2.Name as CountryNameTo,comments.id as IdComment
from (comments,members as m1,members as m2,cities as city1,countries as country1,cities as city2,countries as country2)
where m1.id=IdFromMember and m2.id=IdToMember and m1.Status='Active' and m2.Status='Active' and DisplayableInCommentOfTheMonth='Yes'
and city1.id=m1.IdCity and country1.id=city1.IdCountry
and city2.id=m2.IdCity and country2.id=city2.IdCountry
and comments.id=".$_SESSION["Param"]->IdCommentOfTheMoment ;

        $Data=$this->singleLookup($sql) ;
        return($Data) ;
    }

    /**
     * function UpdateIdCommentOfTheMoment  recomputes the id of the comment of the moment
     */
    public function UpdateIdCommentOfTheMoment() {
        $tt=$this->bulkLookup("select comments.id as IdComment,CommentNbVotes(comments.id) as NbVotes from comments
         order by comments.id desc limit ".$_SESSION["Param"]->NbCommentsInLastComments) ;
        $Max=0 ;
        $IdComment=0 ;
        for ($ii=0;$ii<$_SESSION["Param"]->NbCommentsInLastComments;$ii++) {
            if ($tt[$ii]->NbVotes>$Max) {
                $Max=$tt[$ii]->NbVotes ;
                $IdComment=$tt[$ii]->IdComment ;
            }
        }

        if (($IdComment>0) and (($_SESSION["Param"]->IdCommentOfTheMoment!=$IdComment)or ($_SESSION["Param"]->IdCommentOfTheMoment==0)))  {
            $_SESSION["Param"]->IdCommentOfTheMoment=$IdComment ;
            $sql="update params set IdCommentOfTheMoment=".$IdComment. " limit 1" ;
            $qq = $this->dao->query($sql);
            if (!$qq) {
                throw new PException('UpdateIdCommentOfTheMoment failed for '.$sql.' !');
            }
            return ;
        }
    }


    /**
     * function AddVote adds a vote for the current user and recompute the id of the comment of the moment
        @IdComment is the concerned comment
        it only applies the add the vote to the current user
        if there is already a vote for this user it is replaced in order to prevents duplicated
     */
    public function AddVote($IdComment) {
        $sql="replace into comments_ofthemomment_votes(IdMember,IdComment) values(".$_SESSION["IdMember"].",".$IdComment.")" ;
        $qq = $this->dao->query($sql);
        if (!$qq) {
            throw new PException('AddVote failed for '.$sql.' !');
        }
        $this->UpdateIdCommentOfTheMoment() ;
        MOD_log::get()->write("Add a vote for comment #".$IdComment,"comments") ;
        return ;
    }

    /**
     * function RemoveVote removess a vote for the current user and recompute the id of the comment of the moment
        @IdComment is the concerned comment
        it only applies the add the vote to the current user
     */
    public function RemoveVote($IdComment) {
        $sql="delete from  comments_ofthemomment_votes where IdMember=".$_SESSION["IdMember"]." and IdComment=".$IdComment ;
        $qq = $this->dao->query($sql);
        if (!$qq) {
            throw new PException('RemoveVote failed for '.$sql.' !');
        }
        $this->UpdateIdCommentOfTheMoment() ;
        MOD_log::get()->write("Remove a vote for comment #".$IdComment,"comments") ;
        return ;
    }



}
