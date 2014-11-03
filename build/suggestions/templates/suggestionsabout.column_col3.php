<h2><a href="suggestions"><?php echo $words->get('SuggestionsIntro'); ?></a></h2>
<div class="bw-row">
	<p><?php echo $words->get('SuggestionsIntroText');?></p>
    <a name="suggestionsprocessoverview"></a>
	<h3><?php echo $words->get('SuggestionsProcessOverview');?></h3>
	<table width="99%">
    <colgroup>
        <col width="3%" />
        <col width="12%" />
        <col width="60%" />
        <col width="25%" />
    </colgroup>
        <tr valign="top">
            <td><div class="small grey"><?php echo $words->get('SuggestionsStep');?></div></td>
            <td><div class="small grey"><?php echo $words->get('SuggestionsStepName');?></div></td>
            <td><div class="small grey"><?php echo $words->get('SuggestionsStepDescription');?></div></td>
            <td><div class="small grey"><?php echo $words->get('SuggestionsStepTimeFrame');?></div></td>
        </tr>
        <tr valign="top" class="highlight">
            <td><div class="small grey" style="padding-top:6px">1</div></td>
            <td><h3><a href="/suggestions/create"><?php echo $words->get('SuggestionsCreate');?></a></h3></td>
            <td><p><?php echo $words->get('SuggestionsCreateDescription');?></p></td>
            <td><p><?php echo $words->get('SuggestionsCreateTimeFrame');?></p></td>
        </tr>
        <tr valign="top">
            <td><div class="small grey" style="padding-top:6px">2</div></td>
            <td><h3><a href="/suggestions/approve"><?php echo $words->get('SuggestionsAwaitApproval');?></a></h3></td>
            <td><p><?php echo $words->get('SuggestionsAwaitApprovalDescription');?></p><div class="small grey"><?php echo $words->get('SuggestionsAwaitApprovalVolunteerInfo');?></div></td>
            <td><p><?php echo $words->get('SuggestionsAwaitApprovalTimeFrame');?></p></td>
        </tr>
        <tr valign="top" class="highlight">
            <td><div class="small grey" style="padding-top:6px">3</div></td>
            <td><h3><a href="/suggestions/discuss"><?php echo $words->get('SuggestionsDiscuss');?></a></h3></td>
            <td><p><?php echo $words->get('SuggestionsDiscussDescription');?></p></td>
            <td><p><?php echo $words->get('SuggestionsDiscussTimeFrame');?></p></td>
        </tr>
        <tr valign="top">
            <td><div class="small grey" style="padding-top:6px">4</div></td>
            <td><h3><a href="/suggestions/addoptions"><?php echo $words->get('SuggestionsAddOptions');?></a></h3></td>
            <td><p><?php echo $words->get('SuggestionsAddOptionsDescription');?></p></td>
            <td><p><?php echo $words->get('SuggestionsAddOptionsTimeFrame');?></p></td>
        </tr>
        <tr valign="top" class="highlight">
            <td><div class="small grey" style="padding-top:6px">5</div></td>
            <td><h3><a href="/suggestions/vote"><?php echo $words->get('SuggestionsVote');?></a></h3></td>
            <td><p><?php echo $words->get('SuggestionsVoteDescription');?></p></td>
            <td><p><?php echo $words->get('SuggestionsVoteTimeFrame');?></p></td>
        </tr>
        <tr valign="top">
            <td><div class="small grey" style="padding-top:6px">6</div></td>
            <td><h3><a href="/suggestions/rank"><?php echo $words->get('SuggestionsRank');?></a></h3></td>
            <td><p><?php echo $words->get('SuggestionsRankDescription');?></p></td>
            <td><p><?php echo $words->get('SuggestionsRankTimeFrame');?></p></td>
        </tr>
        <tr valign="top" class="highlight">
            <td><div class="small grey" style="padding-top:6px">7</div></td>
            <td><h3><a href="/suggestions/dev"><?php echo $words->get('SuggestionsDevelopment');?></a></h3></td>
            <td><p><?php echo $words->get('SuggestionsDevelopmentDescription');?></p><div class="small grey"><?php echo $words->get('SuggestionsDevelopmentVolunteerInfo');?></div></td>
            <td><p><?php echo $words->get('SuggestionsDevelopmentTimeFrame');?></p></td>
        </tr>
    </table>
</div><br />
<a name="decisionmakingprocess"></a>
<h3><?php echo $words->get('DecisionMakingProcess'); ?></h3>
<div class="small grey"><?php echo $words->get('DecisionMakingProcessValidity');?></div>
<div class="bw-row">
    <h4><?php echo $words->get('MakingSuggestions');?></h4>
    <p><?php echo $words->get('MakingSuggestionsText');?></p>
    <h4><?php echo $words->get('SuggestionsLifeCycle');?></h4>
    <p><?php echo $words->get('SuggestionsLifeCycleText');?></p>
    <h4><?php echo $words->get('DecisionMakingProcessDetails');?></h4>
    <p><?php echo $words->get('DecisionMakingProcessDetailsText');?></p>
</div>

