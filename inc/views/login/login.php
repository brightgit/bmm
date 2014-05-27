<div class="centerfixedwidth">
		<div style="text-align: center; margin-top: 110px;">
			<img id="main_logo" src="<?php echo base_url("inc/img/admin/app-logo.jpg") ?>" alt="Bright">
		</div>
		<div class="well no-borders-top">
		<h2>Login</h2>
		<form name="form1" method="post" action="index.php?mod=login&view=login">
			<table>
				<tr>
					<td><label>Utilizador</label></td>
					<td><input type="text" name="username" class="txt_input" /></td>
				</tr>
				<tr>
					<td><label>Palavra-Chave</label></td>
					<td><input type="password" name="password" class="txt_input" /><br /></td>
				</tr>
				<tr>
					<td colspan="2">
						<input style="float:right;" type="submit" name="login_submit" value="Entrar" class="btn btn-primary" />
					</td>
				</tr>
			</table>
		</form>                    
	</div>
	
	<br />
	<?php if ($_POST) { echo "<div class='alert alert-warning'><b>Wrong username OR password.</b></div>"; } ?>

	<div class="footer">
		<div class="footer">
			Powered by
			<a class="bright-link" href="http://www.bright.pt/" target="_blank">Bright</a>
			&amp;
			<a style="margin-right:0px;" class="digidoc-link" href="http://digidoc.pt/" target="_blank">Digidoc</a>
		</div>
	</div>
</div>
