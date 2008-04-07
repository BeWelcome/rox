<?php


class MyVisitorsPage extends MemberPage
{
    protected function leftSidebar()
    {
        ?>
          <H3>Action</H3>
          <UL class="linklist" >
            <LI class="icon contactmember16" >
              <A href="contactmember.php?cid=1" >Send message</A>
            </LI>
            <LI class="icon addcomment16" >
              <A href="addcomments.php?cid=1" >Add Comment</A>
            </LI>
          </UL>
        <?php
    }
    
    
    protected function getSubmenuActiveItem()
    {
        return 'visitors';
    }
    
    
    protected function column_col3()
    {
        ?>
          <DIV class="info clearfix" >
            <DIV class="subcolumns" >
              <DIV class="c75l" >
                <DIV class="subcl" >
                  <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=amod"  title="See profile amod" >
                    <IMG class="float_left framed"  src="http://localhost/bw-trunk-new/htdocs/bw/"  height="50px"  width="50px"  alt="Profile" >
                  </A>
                   
                  <P>
                    <STRONG>
                      from 
                      <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=amod" >amod</A>
                    </STRONG>
                  </P>
                  <P>
                    <EM>On the web after some problems with the site</EM>
                  </P>
                   
                  <P>
                    <FONT color="black" >I know the admin</FONT>
                  </P>
                </DIV>
              </DIV>
              <DIV class="c25r" >
                <DIV class="subcr" >
                  <UL class="linklist" >
                    <LI>
                      <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=admin" >admin</A>
                    </LI>
                  </UL>
                  <UL class="linklist" ></UL>
                </DIV>
              </DIV>
            </DIV>
          </DIV>
        <?php
    }
}


?>