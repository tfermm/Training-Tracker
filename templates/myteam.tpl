<script src="/webapp/training-tracker/templates/js/index.js"></script>
<link rel="stylesheet" href="/webapp/training-tracker/templates/css/index.css" type="text/css" />



{box size="16" title="My team"}

	<script>
		$(document).ready(function() {
				{foreach from=$teams  item=staffer name=count}
					$("#{$staffer.wpid}").progressbar("option","value",{$staffer.percent});
				{/foreach}
		});
	</script>

	{if $is_mentor}
		{foreach from=$teams  item=staffer name=count}
			{if $smarty.foreach.count.iteration is even}
				<div id="toolbar" class="light ui-corner-all">
					<a href="/webapp/training-tracker/checklist/{$staffer.wpid}">View/edit {$staffer.name}</a> <a href="/webapp/training-tracker/statistics/{$staffer.wpid}"><div id="{$staffer.wpid}" class="progressbar"></div></a>
				</div>
			{else}
				<div id="toolbar" class="dark ui-corner-all">
					<a href="/webapp/training-tracker/checklist/{$staffer.wpid}">View/edit {$staffer.name}</a> <a href="/webapp/training-tracker/statistics/{$staffer.wpid}"><div id="{$staffer.wpid}" class="progressbar"></div></a>
				</div>
			{/if}

		{/foreach}
	{/if}
{/box}
