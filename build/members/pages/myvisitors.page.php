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
          <h3><?=$words->get('Actions')?></h3>
          <ul class="linklist" >
            <li class="icon contactmember16" >
              <a href="bw/contactmember.php?cid=<?=$member->id?>" ><?=$words->get('ContactMember');?></a>
            </li>
            <li class="icon addcomment16" >
              <a href="bw/addcomments.php?cid=<?=$member->id?>" ><?=$words->get('addcomments');?></a>
            </li>
          </ul>
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
                      var_dump($visitors);
				foreach ($visitors as $v) { 
                    echo "<pre>visitor "; 
					var_dump($v);
					?>
				<?php	
				}
        ?>
          <DIV class="info clearfix" >
            <DIV class="subcolumns" >
              <DIV class="c75l" >
                <DIV class="subcl" >
                  <A href="people/amod"  title="See profile amod" >
                    <IMG class="float_left framed"  src="http://localhost/bw-trunk-new/htdocs/bw/"  height="50px"  width="50px"  alt="Profile" >
                  </A>
                   
                  <P>
                    <STRONG>
                      from 
                      <A href="people/amod" >amod</A>
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
                    <li>
                      <A href="people/admin" >admin</A>
                    </li>
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