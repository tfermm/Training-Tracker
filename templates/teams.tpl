<!-- <script type="text/javascript">
var mentor_string = "{$mentor_string}";
</script>
<script src="templates/js/behavior.js"></script>
-->
{box title="Team builder"}

<style> {* TODO make it not ugly and move to CSS file *}
	#team1, #team2 { list-style-type: none; margin: 0; padding: 0; float: left; margin-right: 10px; border: 1px solid #AAA; padding: 5px; width: 410px;}
	#team1 li, #team2 li { margin: 5px; padding: 5px; font-size: 1.2em; width: 390px; }
</style> 
<script>
	teams = {$teams};
</script>
{literal}
<script>
//TODO move the stuff in the literal tag to its own file	
	$(document).ready(function(){
		$(".chzn-select").chosen(); 
		$(document).on( 'change', 'select.list1', function() { 
			$("#team1").empty();
			for(var i in teams[$(this).val()]){
				$("#team1").append('<li class="ui-state-default" id="' + i + '">' + teams[$(this).val()][i]['name'] + '</li>');
			}	
		});
		$(document).on( 'change', 'select.list2', function() { 
			$("#team2").empty();
			for(var i in teams[$(this).val()]){
				$("#team2").append('<li class="ui-state-default" id="' + i + '">' + teams[$(this).val()][i]['name'] + '</li>');
			}
		});
		function isset(val){
			if(typeof(window[val])!="undefined"){
				return true;
			}
			else{
				return false;
			}
		}
		$( "#team1, #team2" ).sortable({
			connectWith: ".connectedSortable",
			receive: function(event, ui){
				console.log(event);
				//the number of the team it is dropping to is the last character, so this grabs the last character
				listNum = event.target.id.substr(event.target.id.length - 1);
				var mentor_wpid = $(".list"+listNum).val();
				//var mentee_wpid = event.toElement.id;
				var mentee_wpid = event.originalEvent.target.id;
				console.log(mentee_wpid);
				if (listNum == 1){
					//it is removed from list2
					listNumAlt = 2;	
				}
				else{
					//it is removed from list1
					listNumAlt = 1;	
				}
				var mentor_wpid_alt = $(".list"+listNumAlt).val();
				teams[mentor_wpid] //+= teams[mentor_wpid_alt][mentee_wpid]);
				if (_.isUndefined(teams[mentor_wpid]) != true){

					if (mentor_wpid != mentor_wpid_alt && mentor_wpid.length != 0){
						//using jquery for a deep copy
						teams[mentor_wpid][mentee_wpid] = $.extend(true, {}, teams[mentor_wpid_alt][mentee_wpid]);
						delete teams[mentor_wpid_alt][mentee_wpid];
						changeTeam(mentor_wpid, mentee_wpid);
						//call ajax
					}
				}
				else{
					if (mentor_wpid != mentor_wpid_alt && mentor_wpid.length != 0){
						teams[mentor_wpid] = { };
						teams[mentor_wpid][mentee_wpid] = $.extend(true, {}, teams[mentor_wpid_alt][mentee_wpid]);
						delete teams[mentor_wpid_alt][mentee_wpid];
						changeTeam(mentor_wpid, mentee_wpid);
						//call ajax
					}
				}
			}
		}).disableSelection();
		function changeTeam(mentor_wpid, mentee_wpid){
			postData = new Array();
			postData[0] = mentee_wpid;
			postData[1] = mentor_wpid;
			$.ajax({
				type: "POST",
				url: "teams_post",
				data: { data: postData }
			});
		}
	});
</script>
{/literal}

<div class="grid_8 grid-internal table table-bordered dark">
	<br>
	<select data-placeholder="Choose a team leader..." class="chzn-select list1" style="width:90%;" tabindex="2">
		<option value=""></option>
		<option value="unassigned">Unassigned</option> 
		{foreach from=$mentors item=mentor}
			<option value="{$mentor->wpid}">{$mentor->name}</option> 
		{/foreach}
	</select>
<br> <br>
	<ul id="team1" class="connectedSortable ui-sortablei table table-bordered">
	</ul>
</div>


<div class="grid_8 grid-internal table table-bordered dark">
	<br>
	<select data-placeholder="Choose a team leader..." class="chzn-select list2" style="width:90%;" tabindex="2">
		<option value=""></option>
		<option value="unassigned">Unassigned</option> 
		{foreach from=$mentors item=mentor}
			<option value="{$mentor->wpid}">{$mentor->name}</option> 
		{/foreach}
	</select>
<br> <br>
	<ul id="team2" class="connectedSortable ui-sortablei table table-bordered">
	</ul>
</div>
{* Needed space for the menu to load  *}
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<!--
<div id="tabs">
	<ul>
		<li><a href="#mentee-1">Mentee</a></li>
		{foreach from=$mentors item=mentor name=count}
			<li><a href="#mentor{$smarty.foreach.count.index}" id = "{$mentor->wpid}">{$mentor->name}</a></li>
		{/foreach}
	</ul>
	<div id = "mentee-1" class = "mentees">
		<ul id="mentee" class="connected-sortable ui-helper-reset">
			{foreach from=$mentees  item=mentee}
				{if $student->team eq 0}
					<li class="ui-state-higlight" id = "{$student->wpid}">{$student->name}</li>
				{/if}
			{/foreach}
		</ul>
	</div>
	{foreach from=$mentors item=mentor name=count}
		<div id="mentor{$smarty.foreach.count.index}" class = "mentees">
			<ul id="mentor-{$smarty.foreach.count.index}" class = "connected-sortable ui-helper-reset">  
				{foreach from=$mentees  item=mentee}
					{if $student->team_leader == $teacher->wpid}
						<li class="ui-state-higlight" id = "{$student->wpid}">{$student->name}</li>
					{/if}
				{/foreach}		
			</ul>
		</div>
	{/foreach}
</div> -->
{/box}

