<link rel="stylesheet" href="/webapp/training-tracker/templates/css/stats.css" type="text/css" />

<script> //setting global javascript variables
	current_user_wpid = "{$current_user->wpid}";
	active_user_wpid = "{$active_user->wpid}";
	current_user_level = "{$current_user_level}";
	checkboxData = Array();
	{foreach from=$checked item=checkbox}
		checkboxData[{$checkbox}] = "{$checkbox}";
	{/foreach}
</script>

<script src="/webapp/training-tracker/templates/js/checklist.js"></script>

<script>{*setting up progressbars*}
	$(document).ready(function(){
		$("#overall").progressbar("option","value",{$progress});
		{foreach from=$checklist_item_sub_cat item=sub_category}
			$("#{$sub_category.id}").progressbar("option","value",{$sub_category.stat});
		{/foreach}
	});

	{foreach from=$checklist_items item=item}
		$(".{$item.item_id}-div").tooltip()	
	{/foreach}
</script>

{box title=$title}
<br>
{* This is the outer accordian that shows the level ie trainee *}
{foreach from=$checklist_item_cat item=category}
	<h3>{$category.name}</h3><br>
	Overall progress: {$progress}%
	<div id="overall" class="progressbar"></div>
	<br>

	<div id="accordion"> 
		{foreach from=$checklist_item_sub_cat item=sub_category}
			<h3><a href="#">{$sub_category.name} progress: {$sub_category.stat}%<div id="{$sub_category.id}" class="progressbar"></div></a></h3>
			<div id="inner-accordion"> {*  foreach category look at each sub category and add every item per sub category *}
			{foreach from=$checklist_items item=item}
				{if $item.category_id eq $sub_category.id}
					<div id="{$item.id}-div" {if isset($item.updated_by)}title="Last modified by - {$item.updated_by} on {$item.updated_time}"{else}title="This item hasn't been updated yet"{/if}><input class="chkbox" type="checkbox" id="{$item.id}" > {$item.description}</div>
				{/if}
			{/foreach}
			</div>
		{/foreach}
	</div>
{/foreach}

<div class = "people">
	<br><br>
	<textarea class="txtarea" rows="10" cols="40" id="3">{$comments} {* comment section *}
	</textarea>
	<div id="3-output"></div>
	<br />
	<button class="submitButton">Done</button>
	 {* check the persons permission level, if they are above a mentee, show the confirm button *}
	{if $progress eq 100}
		{if ($active_user_level eq 'supervisor' || $active_user_level eq 'shift_leader' || $active_user_level eq 'manager' || $active_user_level eq 'webguru')}
			<br><br>Pressing the confirm button will send an email to your boss saying {$current_user->person()->formatname("f l")} has completed the tasks above<br>
			<button class="confirmButton">Confirm</button>
			<div id="dialog-confirm" title="Are you sure?" > {* Used by the confirm popup *}
				<span class="popup_text">Are you sure you want to confirm {$current_user->person()->formatname("f l")}'s completion of the tasks listed above?</span>
			</div>
		{/if}
	{/if}
</div>
{/box}

