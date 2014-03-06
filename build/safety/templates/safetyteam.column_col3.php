<div class="subcolumns">
        <div class="c50l">
            <div class="subcl">
				<a name="tasks"></a>
				<h3><?php echo $words->get('Safety_Team');?></h3>
				<p><?php echo $words->get('Safety_TeamIntro');?></p>
				<a name="proactive"></a>
				<strong><?php echo $words->get('Safety_Proactive');?></strong>
				<p><?php echo $words->get('Safety_ProactiveText');?></p>
				<a name="reactive"></a>
				<strong><?php echo $words->get('Safety_Reactive');?></strong>
				<p><?php echo $words->get('Safety_ReactiveText');?></p>
            </div>
        </div>
		<div class="c50r">
            <div class="subcr">
				<a name="members"></a>
				<h3><?php echo $words->get('Safety_TeamMembers');?></h3>
				<p><?php echo $words->get('Safety_TeamMembersText', $this->listOfMembers);?></p>
            </div>
		</div>
</div>
<div class="bw-row">
<a name="confidentiality"></a>
<h3><?php echo $words->get('Safety_Confidentiality');?></h3>
<p><?php echo $words->get('Safety_ConfidentialityText');?></p>
</div>