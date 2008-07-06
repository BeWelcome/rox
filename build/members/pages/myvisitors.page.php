<?php


class MyVisitorsPage extends MemberPage
{
    protected function leftSidebar()
    {
    	$member = $this->member;
    	//print_r($this->member);
    	//$lang = $this->model->get_profile_language();
		//$lang = $this->member->get_profile_language();
		//$profile_language = $lang->id;
		//$profile_language_code = $lang->ShortCode;
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
        return 'visitors';
    }
    
    
    protected function column_col3()
    {
    	$member = $this->member;
    	$visitors = $member->visitors;
    	//$visitors = $member->relations;
    	?>
    	
				<?php
				//echo "<pre>visitor "; 
				foreach ($visitors as $v) { 
					//print_r($v);
					?>
				<?php	
				}
				?>              
    	
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