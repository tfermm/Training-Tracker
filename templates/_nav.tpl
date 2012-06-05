<ul class ="grid_16">
		<li><a href="/webapp/training-tracker/">Person select</a></li>
		<li><a href="/webapp/training-tracker/viewteams">View teams</a></li>
		{if $is_mentor && $has_team}
			<li><a href="/webapp/training-tracker/myteam/{$wpid}">View my team</a></li>
		{/if}
		{if $is_admin}
			<li><a href="/webapp/training-tracker/teams">Team builder</a></li>
			<li><a href="/webapp/training-tracker/admin">admin</a></li>
		{/if}
		
</ul>
