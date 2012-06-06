<script type="text/javascript">
var mentor_string = "{$mentor_string}";
</script>
<script src="templates/js/behavior.js"></script>

{box title="Team builder"}
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
</div>
{/box}

