{* TODO: move js script tags to _js.tpl.  Include the script tags a la {PSU_JS src="url/to/file"}  *}
{*<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/smoothness/jquery-ui.css" type="text/css" media="all" />*}
{*<link rel="stylesheet" href = "templates/style.css" type = "text/css" media = "all" />*}

{* TODO: move styles to _css.tpl Include css files a la {PSU_CSS href="url/to/file"} *}
{*<script src="templates/code.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
*}
<script type="text/javascript">
var mentor_string = "{$mentor_string}";
</script>
<script src="templates/js/behavior.js"></script>

{box}
<div id="tabs">
	<ul>
		<li><a href="#mentee-1">Mentee</a></li>
		{foreach from=$mentor item=teacher name=count}
			<li><a href="#mentor{$smarty.foreach.count.index}" id = "{$teacher.wpid}">{$teacher.name}</a></li>
		{/foreach}
	</ul>
	<div id = "mentee-1" class = "mentees">
		<ul id="mentee" class="connected-sortable ui-helper-reset">
			{foreach from=$mentee  item=student}
				{if $student.team eq 0}
					<li class="ui-state-higlight" id = "{$student.wpid}">{$student.name}</li>
				{/if}
			{/foreach}
		</ul>
	</div>
	{foreach from=$mentor item=teacher name=count}
		<div id="mentor{$smarty.foreach.count.index}" class = "mentees">
			<ul id="mentor-{$smarty.foreach.count.index}" class = "connected-sortable ui-helper-reset">  
				{foreach from=$mentee  item=student}
					{if $student.team_leader == $teacher.wpid}
						<li class="ui-state-higlight" id = "{$student.wpid}">{$student.name}</li>
					{/if}
				{/foreach}		
			</ul>
		</div>
	{/foreach}
</div>
{/box}

