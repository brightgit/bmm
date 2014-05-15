<?php

class ViewLogin {
	
	private $login = NULL;

	function __construct($obj, $core) {

		$this->login = $obj;
		if (isset($_POST))
			if (!$this->login->doLogin($core->getTools()->getPost('username'), $core->getTools()->getPost('password')))
				$this->show();
			else{
				echo '<meta http-equiv="refresh" content="0; url=index.php?mod=dashboard">';
				return false;
			}
		else
			$this->show();

		}

		function __destruct() {
			$this->login = null;
			unset($this->login);
		}

		function show() { ?>
		<div class="centerfixedwidth">
			<div style="text-align: right; margin-top:20%; margin-bottom: -40px; background:#ec3424;">
				<img style="margin-right: 40px;" id="main_logo" src="http://holmes.bright.pt/bmm/inc/img/admin/holmes-place-logo.jpg" alt="Bright">
			</div>
			<div class="well no-borders-top">
				<h2>Login</h2>
				<form name="form1" method="post" action="">
					<table>
						<tr>
							<td><label>Utilizador</label></td>
							<td><input type="text" name="username" class="txt_input" /></td>
						</tr>
						<tr>
							<td><label>Palavra-Chave</label></td>
							<td><input type="password" name="password" class="txt_input" /><br /><br /></td>
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
		<?php
	}
}

?>

