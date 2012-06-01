<script src="templates/js/behavior.js"></script>

{box title="Teams"}
{foreach from=$team_array item=team}
	<h4>{$team.mentor}</h4>
	{foreach from=$team.mentees item=mentee}
		{$mentee}<br>
	{/foreach}
	<br>
{*	{$team_array|@debug_print_var}<br><br> *}
{/foreach}
{/box}
