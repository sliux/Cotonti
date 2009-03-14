<!-- BEGIN: POLL_VIEW -->
{POLL_FORM_BEGIN}
<table class="cells">
<!-- BEGIN: POLLTABLE -->
<tr>
	<td><label>{POLL_INPUT}{POLL_OPTIONS}</label></td>
	<td>
		<div style="width:256px;">
			<div class="bar_back">
				<div class="bar_front" id="pr_{POLL_PER}"></div>
			</div>
		</div>
	</td>
	<td>{POLL_PER}%</td>
	<td>{POLL_COUNT}</td>
</tr>
<!-- END: POLLTABLE -->
<tr>
	<td colspan="4">{POLL_FORM_BUTTON}</td>
</tr>
</table>
<script type="text/javascript">
	function anim(){
		$(".bar_front").each(function(){
			var percentage = Math.floor(($(this).attr("id").replace('pr_','')*256)/100)+'px';
			if ($(this).attr("id")!=""){$(this).css({width:"0"}).animate({width: percentage}, "slow");}
			$(this).attr("id","");
		});
	}
	anim();
</script>
<p>{POLL_VOTERS} {PHP.skinlang.polls.voterssince} {POLL_SINCE}</p>
{POLL_FORM_END}
<!-- END: POLL_VIEW -->


<!-- BEGIN: POLL_VIEW_VOTED -->
<table class="cells">
<!-- BEGIN: POLLTABLE -->
<tr>
	<td>{POLL_OPTIONS}</td>
	<td>
		<div style="width:256px;">
			<div class="bar_back">
				<div class="bar_front" id="pr_{POLL_PER}"></div>
			</div>
		</div>
	</td>
	<td>{POLL_PER}%</td>
	<td>{POLL_COUNT}</td>
</tr>
<!-- END: POLLTABLE -->
<tr>
	<td colspan="4">{PHP.L.polls_alreadyvoted}</td>
</tr>
</table>
<script type="text/javascript">
	function anim(){
		$(".bar_front").each(function(){
			var percentage = Math.floor(($(this).attr("id").replace('pr_','')*256)/100)+'px';
			if ($(this).attr("id")!=""){$(this).css({width:"0"}).animate({width: percentage}, "slow");}
			$(this).attr("id","");
		});
	}
	anim();
</script>
<p>{POLL_VOTERS} {PHP.skinlang.polls.voterssince} {POLL_SINCE}</p>
<!-- END: POLL_VIEW_VOTED-->

<!-- BEGIN: POLL_VIEW_LOCKED -->
<table class="cells">
<!-- BEGIN: POLLTABLE -->
<tr>
	<td>{POLL_OPTIONS}</td>
	<td>
		<div style="width:256px;">
			<div class="bar_back">
				<div class="bar_front" style="width:{POLL_PER}%;"></div>
			</div>
		</div>
	</td>
	<td>{POLL_PER}%</td>
	<td>{POLL_COUNT}</td>
</tr>
<!-- END: POLLTABLE -->
<tr>
	<td colspan="4">{PHP.L.polls_alreadyvoted}</td>
</tr>
</table>
<p>{POLL_VOTERS} {PHP.skinlang.polls.voterssince} {POLL_SINCE}</p>
<!-- END: POLL_VIEW_LOCKED-->

<!-- BEGIN: POLL_VIEW_DISABLED -->
<table class="cells">
<!-- BEGIN: POLLTABLE -->
<tr>
	<td>{POLL_OPTIONS}</td>
</tr>
<!-- END: POLLTABLE -->
<tr>
	<td>{PHP.L.rat_registeredonly}</td>
</tr>
</table>
<!-- END: POLL_VIEW_DISABLED-->

<!-- BEGIN: POLL_EDIT_FORM -->
<tr><td>{PHP.L.adm_polls_polltopic}</td><td><input type="text" class="text" name="poll_text" value="{EDIT_POLL_TEXT}" size="64" maxlength="255" /></td></tr>

<tr><td>{PHP.L.Options}</td><td>{EDIT_POLL_OPTIONS}</td></tr>
<tr><td></td><td><label>{EDIT_POLL_MULTIPLE}{PHP.L.polls_multiple}</label>
<!-- BEGIN: EDIT -->
<br /><label>{EDIT_POLL_CLOSE}{PHP.L.Close}</label>
<br /><label>{EDIT_POLL_RESET}{PHP.L.Reset}</label>
<br /><label>{EDIT_POLL_DELETE}{PHP.L.Delete}</label>
<!-- END: EDIT -->
</td></tr>
<!-- END: POLL_EDIT_FORM -->


<!-- BEGIN: MAIN -->
	<!-- BEGIN: POLLS_VIEW -->
	<div class="mboxHD">{POLLS_TITLE}</div>
	<div class="mboxBody">
	{POLLS_FORM}
			{PHP.skinlang.polls.Comments} {POLLS_COMMENTS}{POLLS_COMMENTS_DISPLAY}
	</div>
	<!-- END: POLLS_VIEW -->

	<!-- BEGIN: POLLS_VIEWALL -->
	<div class="mboxHD">{PHP.skinlang.poll.Allpolls}</div>
	<div class="mboxBody">
		{POLLS_LIST}
	</div>
	<!-- END: POLLS_VIEWALL -->

	<!-- BEGIN: POLLS_EXTRA -->
	<div class="block">{POLLS_EXTRATEXT}<br />{POLLS_VIEWALL}</div>
	<!-- END: POLLS_EXTRA -->
<!-- END: MAIN -->