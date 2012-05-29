<script>
	current_user_wpid = "{$current_user.wpid}";
	active_user_wpid = "{$active_user.wpid}";
	current_user_level = "{$current_user_level}";
	checkboxData = "{$checked}";
 </script>

<script src="/webapp/training-tracker/templates/js/checklist.js"></script>

<div id="headder">
	<div id="toolbar" class="ui-widget-header ui-corner-all">
  	<button id="team_builder">Team builder</button>
		<button id="view_teams">View teams</button>
		<button id="cklist">Person select</button>
	</div>
</div>
<br>

{box title=$title}
<br><br>
<div id = "outer-accordion"> {* This is the outer accordian that shows the level ie trainee *}
{foreach from=$checklist_item_cat item=category}
	<h2><a href="#">{$category.name}</a></h2>
	<div id="accordion"> 
		{foreach from=$checklist_item_sub_cat item=sub_category}
			{if $sub_category.slug eq $current_user_level}
			<h3><a href="#">{$sub_category.name}</a></h3>
			<div id="inner-accordion">
			{foreach from=$checklist_items item=item name=count}
				{if $item.slug eq $sub_category.sub_category}
					<input class="chkbox" type="checkbox" id="{$item.slug}{$smarty.foreach.count.iteration}"> {$item.description}<div id ="{$item.slug}{$smarty.foreach.count.iteration}-output"></div>
				{/if}
			{/foreach}
			</div>
			{/if}
		{/foreach}
	</div>
{/foreach}
</div>
<div class = "people">
	<br><br>
	<textarea class="txtarea" rows="10" cols="40" id="3">{$comments}
	</textarea>
	<div id="3-output"></div>
	<br />
	<button class="submitButton">Done</button>
	{if ($active_user_level eq 'supervisor' || $active_user_level eq 'shift_leader' || $active_user_level eq 'manager' || $active_user_level eq 'webguru')}
		<br><br>Pressing the confirm button will send an email to your boss saying {$current_user.name} has completed the tasks above<br>
		<button class="confirmButton">Confirm</button>
	{/if}
</div>
{/box}
{* {$mentee1|@debug_print_var} *}

