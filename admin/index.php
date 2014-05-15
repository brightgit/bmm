<?php

header("Content-Type: text/html; charset=utf-8");

include('../inc/Core.php');
$core = new Core('bo');

if (isset($_GET['logout']) || isset($_GET['sdestroy'])):
	session_destroy();
unset($_SESSION);
//header("Location: http://pme24.net/holmes/bmm/admin/" );
endif;

$debug = new Debug();
$tools = $core->getTools();

?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Bright - Mailing Management</title>
	<link href="../inc/libs/jquery-ui/bootstrap/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
	<link href="../inc/css/admin.css" rel="stylesheet" type="text/css" />
	<link href="../inc/js/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="../inc/js/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Open+Sans|Numans' rel='stylesheet' type='text/css' />
	<link href="../inc/css/visualize.css" rel="stylesheet" type="text/css" />
	<link href="../inc/js/morris/morris.css" rel="stylesheet" type="text/css" />

	<link href="../inc/css/visualize-custom.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css" />


	<script type="text/javascript">
	function toogleConteudo(e){
		//var mod = ele.options[ele.slectedIndex].value;
		var mod = e.options[e.selectedIndex].value;
		if(mod!=1){
			document.getElementById('toogle_conteudo1').style.display='none';
			document.getElementById('toogle_conteudo2').style.display='none';
		}else{
			document.getElementById('toogle_conteudo1').style.display='table-row';
			document.getElementById('toogle_conteudo2').style.display='table-row';
		}
	}
	</script>
</head>

<body id="internal">
	<?php

	if(!isset($_SESSION['user'])){
		$core->getMod('login');
	}else if($mod!='login'){?>
	<div class="bright-bo">
		<div class="header">
			<div class="logo">
				<a href='http://pme24.net/holmes/bmm/admin/index.php?mod=dashboard'>
					<img alt="" src="../inc/img/admin/logo.png" />
				</a>
			</div>
			<div style="float:right;">
				<div class="links">
					<span class="user-authenticated"><i class="icon-white icon-lock"></i> Autenticado como <u><?php echo $core->user->first_name . " " .  $core->user->last_name ?></u> </span>
					<div class="btn-group">
						<?php 
						$query = "SELECT id FROM mensagens_enviadas";
						$res = mysql_query($query);
						$num_espera = mysql_num_rows($res);
						 ?>
						<span class="btn btn-inverse disabled font-10">v2.1.0</span>
						<span class="btn btn-inverse disabled font-10">Em Espera: <?php echo $num_espera; ?></span>

						<!-- definicoes -->
						<?php if($core->user->is_admin): ?>
						<a class="btn btn-inverse text-white font-10" href='?mod=settings'><i class="icon-white icon-cog"></i> <?php echo _('Defini&ccedil;&otilde;es');?></a>
						<?php endif; ?>

						<!-- force send DEVELOPER ONLY -->
						<?php if($core->user->is_admin && false): ?>
						<a target="_blank" rel="tooltip" href="http://<?php echo $_SERVER["HTTP_HOST"] ?>/bmm/inc/send_email.php?api=<?php echo $core->settings->sender_api_key ?>"  class="btn btn-inverse	 text-white font-10" href="?mod=newsletter&amp;view=utilizadores"><i class="icon-white icon-exclamation-sign"></i> For&ccedil;ar envio</a>
						<?php endif; ?>

						<!-- check bounces DEVELOPER ONLY -->
						<?php if($core->user->is_admin  && false): ?>
						<a target="_blank" rel="tooltip" href="http://<?php echo $_SERVER["HTTP_HOST"] ?>/bmm/inc/check_bounces.php" class="btn btn-inverse text-white font-10" href="?mod=newsletter&amp;view=utilizadores"><i class="icon-white icon-exclamation-sign"></i> 	Bounces</a>
						<?php endif; ?>
						<a class="btn btn-danger text-white font-10" href='?logout'><i class="icon-white icon-off"></i> <?php echo _('Logout');?></a>
					</div>
				</div>
			</div>
			<div class="clear"></div>
			<div class="navbar">
				<div class="navbar-inner">
					<ul class="nav">
						<li <?php if ( $_GET['mod']=='dashboard' && !isset($_GET['view'])) echo 'class="active"'; ?>><a href="?mod=dashboard"><?php echo _('Dashboard');?></a></li>
						<li <?php if ( $_GET['mod']=='newsletter' && $_GET['view']=='categorias') echo 'class="active"'; ?>><a href="?mod=newsletter&amp;view=categorias">Grupos</a></li>
						<li <?php if ( $_GET['mod']=='newsletter' && !isset($_GET['view'])) echo 'class="active"'; ?>><a href="?mod=newsletter"><?php echo _('Subscritores');?></a></li>
						<li <?php if ( $_GET['mod']=='newsletter' && $_GET['view']=='messages') echo 'class="active"'; ?>><a href="?mod=newsletter&amp;view=messages">Newsletters</a></li>
						<li <?php if ( $_GET['mod']=='newsletter' && $_GET['view']=='statistics') echo 'class="active"'; ?>><a href="?mod=newsletter&amp;view=statistics">Estat&iacute;sticas</a></li>						
						<?php if($core->user->is_admin): ?>
						<li <?php if ( $_GET['mod']=='newsletter' && $_GET['view']=='utilizadores') echo 'class="active"'; ?>><a href="?mod=newsletter&amp;view=utilizadores">Utilizadores</a></li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<div class="content"><?php $core->getMod(); ?></div>	
		<div class="footer">
			Powered by
			<a class="bright-link" href="http://www.bright.pt/" target="_blank">Bright</a>
			&amp;
			<a class="digidoc-link" href="http://digidoc.pt/" target="_blank">Digidoc</a>
		</div>
	</div>

	<?php } ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="../inc/libs/jquery-ui/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="../inc/js/tablesort.js"></script>
<script type="text/javascript" src="../inc/js/tablesorter.pager.js"></script>
<script type="text/javascript" src="../inc/js/visualize.jQuery.js"></script>
<script type="text/javascript" src="../inc/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../inc/js/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../inc/js/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript" src="../inc/libs/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="../inc/libs/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript" src="../inc/js/validator/jquery.validate.min.js"></script>
<script type="text/javascript" src="../inc/js/validator/messages_pt_PT.js"></script>
<script type="text/javascript" src="../inc/js/morris/morris.min.js"></script>
<script type="text/javascript" src="../inc/js/onload_admin.js"></script>
<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script type="text/javascript" src="http://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>

</body>
</html>

<?php

/*
 * CHAMAR $core->_destruct
 */
$core->__destruct();

?>