{PSU_JS src="/webapp/training-tracker/js/index.js"}
{PSU_CSS href="/webapp/training-tracker/css/index.css"}
{* TODO: OMG SPACES? Yea Bro. Spaces are the new thing. *}

{box size="16" title="Person selection"}
	{if $is_mentor}
		{foreach from=$staff  item=staffer }
			<div class="staff light ui-corner-all smoothness" >
				<a href="staff/statistics/{$staffer->wpid}">View/edit {$staffer->person()->formatName("f l")}</a> 
				<div id="{$staffer->wpid}" class="progressbar" data-progress="{$staffer->stats('progress')}"></div>
			</div>
		{/foreach}
	{else}

		<div class="staff light ui-corner-all smoothness" data-progress="{$staff->stats('progress')}">
			<a href="staff/statistics/{$user->wpid}">View/edit {$staff->person()->formatName("f l")}</a>
			<div id="{$staff->wpid}" data-progress="{$staff->stats('progress')}" class="progressbar"></div>
		</div>
	{/if}
{/box}
