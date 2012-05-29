<style>
	</style>
{box size="16" title="Welcome"}
{if $is_mentor}
	{foreach from=$staff  item=staffer name=count}
		{if $smarty.foreach.count.iteration is even}
			<div id="toolbar" class="light ui-corner-all">
				<a href="checklist/{$staffer.wpid}">View/edit {$staffer.name}</a>
			</div>
		{else}
			<div id="toolbar" class="dark ui-corner-all">
				<a href="checklist/{$staffer.wpid}">View/edit {$staffer.name}</a>
			</div>
		{/if}

	{/foreach}
{else}
	<div id="toolbar" class="light ui-corner-all">
		<a href="checklist/{$current_user.wpid}">View/edit {$current_user.name}</a><br>
	</div>
{/if}


{/box}
