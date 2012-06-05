<script> //setting global javascript variables
	current_user_wpid = "{$current_user.wpid}";
	active_user_wpid = "{$active_user.wpid}";
	current_user_level = "{$current_user_level}";
	checkboxData = Array();
	{foreach from=$checked item=checkbox name=ct}
		checkboxData[{$checkbox}] = "{$checkbox}"; 
	{/foreach}
	
</script>

<script src="/webapp/training-tracker/templates/js/checklist.js"></script>

{box title=$title}
<br>
{foreach from=$checklist_item_cat item=category}
	<h3>{$category.name}</h3>
	<br>
	<div id="accordion"> 
		{foreach from=$checklist_item_sub_cat item=sub_category}
			<h3><a href="#">{$sub_category.name}</a></h3>
			<div id="inner-accordion"> {*  foreach category look at each sub category and add every item per sub category *}
			{foreach from=$checklist_items item=item}
				{if $item.category_id eq $sub_category.id}
					<input class="chkbox" type="checkbox" id="{$item.id}"> {$item.description}<br>
				{/if}
			{/foreach}
			</div>
		{/foreach}
	</div>
{/foreach}
<div class = "people">
	<br><br>
	<!-- Bond, James Bond -->
	<textarea class="txtarea" rows="10" cols="40" id="007">{$comments} {* comment section *}
	</textarea>
	<div id="3-output"></div>
	<br />
	<button class="submitButton">Done</button>
	 {* check the persons permission level, if they are above a mentee, show the confirm button *}
	{if $progress eq 100}
		{if ($active_user_level eq 'supervisor' || $active_user_level eq 'shift_leader' || $active_user_level eq 'manager' || $active_user_level eq 'webguru')}
			<br><br>Pressing the confirm button will send an email to your boss saying {$current_user.name} has completed the tasks above<br>
			<button class="confirmButton">Confirm</button>
			<div id="dialog-confirm" title="Are you sure?" > {* Used by the confirm popup *}
				<span class="popup_text">Are you sure you want to confirm {$current_user.name}'s completion of the tasks listed above?</span>
			</div>
		{/if}
	{/if}
</div>
{/box}
{* {$checked|@debug_print_var} *}

