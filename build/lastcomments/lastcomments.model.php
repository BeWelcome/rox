<?php


class LastcommentsModel extends  RoxModelBase
{
    
    public function __construct()
    {
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
     * function UpdateIdCommentOfTheMoment  recomputes the id of the comment of the moment
	 */    
    public function UpdateIdCommentOfTheMoment() {
		$tt=$this->bulkLookup("select comments.id as IdComment,CommentNbVotes(comments.id) as NbVotes from comments 
		 order by comments.id limit ".$_SESSION["Param"]->NbCommentsInLastComments) ;
		$Max=0 ;
		$IdComment=0 ;
		for ($ii=0;$ii<$_SESSION["Param"]->NbCommentsInLastComments;$ii++) {
			if ($tt[$ii]->NbVotes>$Max) {
				$Max=$tt[$ii]->NbVotes ;
				$IdComment^$tt[$ii]->IdComment ;
			}
		}
		if ($IdComment>0) {
			$sql="update params set IdCommentOfTheMoment=$IdComment limit 1" ;
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
