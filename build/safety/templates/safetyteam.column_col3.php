<div class="row mt-3">
    <div class="col-12 col-lg-6">
        <a name="tasks"></a>
        <h3><?php echo $words->get('Safety_Team');?></h3>
        <p><?php echo $words->get('Safety_TeamIntro');?></p>
        <a name="proactive"></a>
        <h5><?php echo $words->get('Safety_Proactive');?></h5>
        <p><?php echo $words->get('Safety_ProactiveText');?></p>
        <a name="reactive"></a>
        <h5><?php echo $words->get('Safety_Reactive');?></h5>
        <p><?php echo $words->get('Safety_ReactiveText');?></p>
    </div>
    <div class="col-12 col-lg-6 card p-2">
        <a name="members"></a>
        <h3><?php echo $words->get('Safety_TeamMembers');?></h3>
        <p><?php echo $words->get('Safety_TeamMembersText', $this->listOfMembers);?></p>
    </div>
    <div class="col-12 mt-3">
        <a name="confidentiality"></a>
        <h3><?php echo $words->get('Safety_Confidentiality');?></h3>
        <p><?php echo $words->get('Safety_ConfidentialityText');?></p>
    </div>
</div>