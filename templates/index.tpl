<script src="/webapp/training-tracker/templates/js/index.js"></script>
<link rel="stylesheet" href="/webapp/training-tracker/templates/css/index.css" type="text/css" />



{box size="16" title="Person selection"}

<script>
	$(document).ready(function() {
		{if $is_mentor}
			{foreach from=$staff  item=staffer name=count}
				$("#{$staffer.wpid}").progressbar("option","value",{$staffer.percent});
			{/foreach}
		{else}
				$("#{$current_user.wpid}").progressbar("option","value",{$current_user.percent});
		{/if}
	});
</script>

{if $is_mentor}
	{foreach from=$staff  item=staffer name=count}
		{if $smarty.foreach.count.iteration is even}
			<div id="toolbar" class="light ui-corner-all">
				<a href="checklist/{$staffer.wpid}">View/edit {$staffer.name}</a> <a href="statistics/{$staffer.wpid}"><div id="{$staffer.wpid}" class="progressbar"></div></a>
			</div>
		{else}
			<div id="toolbar" class="dark ui-corner-all">
				<a href="checklist/{$staffer.wpid}">View/edit {$staffer.name}</a> <a href="statistics/{$staffer.wpid}"><div id="{$staffer.wpid}" class="progressbar"></div></a>
			</div>
		{/if}

	{/foreach}
{else}
	<div id="toolbar" class="light ui-corner-all">
		<a href="checklist/{$current_user.wpid}">View/edit {$current_user.name}</a> <a href="statistics/{$current_user.wpid}"><div id="{$current_user.wpid}" class="progressbar">{* <span class="progressbar_text">Statisticial view/edit for {$current_user.name}</span> *}</div></a>
	</div>
{/if}
{/box}
