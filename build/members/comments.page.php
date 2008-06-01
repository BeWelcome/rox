<?php


class CommentsPage extends MemberPage
{
    protected function leftSidebar()
    {
    	$words = $this->getWords();
        ?>
          <H3><?=$words->get('Actions')?></H3>
          <UL class="linklist" >
            <LI class="icon contactmember16" >
              <A href="contactmember.php?cid=<?=$member->id?>" ><?=$words->get('ContactMember');?></A>
            </LI>
            <LI class="icon addcomment16" >
              <A href="addcomments.php?cid=<?=$member->id?>" ><?=$words->get('addcomments');?></A>
            </LI>
          </UL>
        <?php
    }
    
    
    protected function getSubmenuActiveItem()
    {
        return 'comments';
    }
    
    
    protected function column_col3()
    {
    	$comments = $this->member->comments;
    	foreach ($comments as $c) {
    		//echo "comment: ";
    		//print_r($c);
    	}
        ?>
          <DIV class="info clearfix" >
            <DIV class="subcolumns" >
              <DIV class="c75l" >
                <DIV class="subcl" >
                  <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=admin"  title="See profile admin" >
                    <IMG class="float_left framed"  src="http://localhost/bw-trunk-new/htdocs/bw/"  height="50px"  width="50px"  alt="Profile" >
                  </A>
                   
                  <P>
                    <STRONG>
                      from 
                      <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=admin" >admin</A>
                    </STRONG>
                  </P>
                  <P>
                    <EM>
                      <FONT color="gray" >
                        <FONT size="1" >comment date November 24, 2006, 9:59 am (UTC)</FONT>
                      </FONT>
                      <BR>
                      In a library
                    </EM>
                  </P>
                   
                  <P>
                    <FONT color="#4e9a06" >
                      <HR>
                      <FONT color="gray" >
                        <FONT size="1" >comment date February 20, 2007, 2:03 pm (UTC)</FONT>
                      </FONT>
                      <BR>
                      <BR>
                      <HR>
                      <FONT color="gray" >
                        <FONT size="1" >comment date February 20, 2007, 2:10 pm (UTC)</FONT>
                      </FONT>
                      <BR>
                      <BR>
                      <HR>
                      <FONT color="gray" >
                        <FONT size="1" >comment date February 20, 2007, 2:11 pm (UTC)</FONT>
                      </FONT>
                      <BR>
                      <BR>
                      <HR>
                      <FONT color="gray" >
                        <FONT size="1" >comment date February 20, 2007, 2:12 pm (UTC)</FONT>
                      </FONT>
                      <BR>
                      new
                      <BR>
                    </FONT>
                  </P>
                  <P></P>
                </DIV>
              </DIV>
              <DIV class="c25r" >
                <DIV class="subcr" >
                  <UL class="linklist" >
                    <LI>
                      <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=henri" >henri</A>
                    </LI>
                    <LI>She/he belongs to my family</LI>
                  </UL>
                  <UL class="linklist" >
                    <LI>
                      <A href="feedback.php?IdCategory=4" >Report a problem with this comment</A>
                    </LI>
                  </UL>
                </DIV>
              </DIV>
            </DIV>
          </DIV>
          <DIV class="info highlight clearfix" >
            <DIV class="subcolumns" >
              <DIV class="c75l" >
                <DIV class="subcl" >
                  <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=jeanyves"  title="See profile jeanyves" >
                    <IMG class="float_left framed"  src="http://localhost/bw-trunk-new/htdocs/bw/"  height="50px"  width="50px"  alt="Profile" >
                  </A>
                   
                  <P>
                    <STRONG>
                      from 
                      <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=jeanyves" >jeanyves</A>
                    </STRONG>
                  </P>
                  <P>
                    <EM>
                      <FONT color="gray" >
                        <FONT size="1" >comment date January 5, 2007, 1:03 pm (UTC)</FONT>
                      </FONT>
                      <BR>
                      On my computer and in my library
                    </EM>
                  </P>
                   
                  <P>
                    <FONT color="#4e9a06" >In fact I created this fake profile to illustrate what could be the bWelcome profiles</FONT>
                  </P>
                </DIV>
              </DIV>
              <DIV class="c25r" >
                <DIV class="subcr" >
                  <UL class="linklist" >
                    <LI>
                      <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=henri" >henri</A>
                    </LI>
                  </UL>
                  <UL class="linklist" >
                    <LI>
                      <A href="feedback.php?IdCategory=4" >Report a problem with this comment</A>
                    </LI>
                  </UL>
                </DIV>
              </DIV>
            </DIV>
          </DIV>        
        <?php
    }
}


?>