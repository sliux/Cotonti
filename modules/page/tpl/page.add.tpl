<!-- BEGIN: MAIN -->

		{FILE ./themes/nemesis/warnings.tpl}

		<div class="block">
			<h2 class="page">{PAGEADD_PAGETITLE}</h2>
			<form action="{PAGEADD_FORM_SEND}" enctype="multipart/form-data" method="post" name="pageform">
				<table class="cells">
					<tr>
						<td class="width30">{PHP.L.Category}:</td>
						<td class="width70">{PAGEADD_FORM_CAT}</td>
					</tr>
					<tr>
						<td>{PHP.L.Title}:</td>
						<td>{PAGEADD_FORM_TITLE}</td>
					</tr>
					<tr>
						<td>{PHP.L.Description}:</td>
						<td>{PAGEADD_FORM_DESC}</td>
					</tr>
					<tr>
						<td>{PHP.L.Author}:</td>
						<td>{PAGEADD_FORM_AUTHOR}</td>
					</tr>
					<tr>
						<td>{PHP.L.Extrakey}</td>
						<td>{PAGEADD_FORM_KEY}</td>
					</tr>
					<tr>
						<td>{PHP.L.Alias}:</td>
						<td>{PAGEADD_FORM_ALIAS}</td>
					</tr>
					<!-- BEGIN: TAGS -->
					<tr>
						<td>{PAGEADD_TOP_TAGS}:</td>
						<td>{PAGEADD_FORM_TAGS} ({PAGEADD_TOP_TAGS_HINT})</td>
					</tr>
					<!-- END: TAGS -->
					<tr>
						<td>{PHP.L.Owner}:</td>
						<td>{PAGEADD_FORM_OWNER}</td>
					</tr>
					<tr>
						<td>{PHP.L.Begin}:</td>
						<td>{PAGEADD_FORM_BEGIN}</td>
					</tr>
					<tr>
						<td>{PHP.L.Expire}:</td>
						<td>{PAGEADD_FORM_EXPIRE}</td>
					</tr>
					<tr>
						<td colspan="2">
							{PAGEADD_FORM_TEXT}
							<!-- IF {PAGEADD_FORM_PFS} -->{PAGEADD_FORM_PFS}<!-- ENDIF -->
							<!-- IF {PAGEADD_FORM_SFS} --><span class="spaced">{PHP.cfg.separator}</span>{PAGEADD_FORM_SFS}<!-- ENDIF -->
						</td>
					</tr>			
					<tr>
						<td>{PHP.themelang.pageadd.File}:</td>
						<td>
							{PAGEADD_FORM_FILE}
							<p>{PHP.themelang.pageadd.Filehint}</p>
						</td>
					</tr>
					<tr>
						<td>{PHP.L.URL}:<br />{PHP.themelang.pageadd.URLhint}</td>
						<td>{PAGEADD_FORM_URL}<br />{PAGEADD_FORM_PFS_URL_USER} &nbsp; {PAGEADD_FORM_PFS_URL_SITE}</td>
					</tr>
					<tr>
						<td>{PHP.themelang.pageadd.Filesize}:<br />{PHP.themelang.pageadd.Filesizehint}</td>
						<td>{PAGEADD_FORM_SIZE}</td>
					</tr>
					<tr>
						<td colspan="2" class="valid">
						<!-- IF {PHP.usr_can_publish} -->
						<input name="rpublish" type="submit" class="submit" value="{PHP.L.Publish}"
							onclick="this.value='OK';return true" />
						<input type="submit" value="{PHP.L.Putinvalidationqueue}" />
						<!-- ELSE -->
						<input type="submit" value="{PHP.L.Submit}" />
						<!-- ENDIF -->
						</td>
					</tr>
				</table>
			</form>
		</div>
		
		<div class="help">{PHP.themelang.pageadd.Formhint}</div>

<!-- END: MAIN -->
