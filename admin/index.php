<?php

header("Content-Type: text/html; charset=utf-8");

require_once('../inc/Core.php');
if (!isset($_GET["mod"])) {
	$_GET["mod"] = "dashboard";
}

if ( !isset($_SESSION['user'] ) ) {
	$_GET["mod"] = "login";
}
$core = new Core('bo');

$tools = $core->getTools();


// É necessário carregar aqui o módulo. Antes de mostrar qualquer coisa.
$core->load_mod( $_GET["mod"] );	//carregar o módulo, não pode ser mostrado agora

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
	<div class="bright-bo">
		<?php if ( $core->load_menu !== FALSE): ?>
		<div class="header">
			<div class="logo">
				<a href='index.php?mod=dashboard'>
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
						<span class="btn btn-inverse disabled font-10">v3.0.0</span>
					<span class="btn btn-inverse disabled font-10">Em Espera: <?php echo $num_espera; ?></span>

						<!-- definicoes -->
						<?php if($core->user->is_admin): ?>
						<a class="btn btn-inverse text-white font-10" href='?mod=settings_list'><i class="icon-white icon-cog"></i> <?php echo _('Defini&ccedil;&otilde;es');?></a>
					<?php endif; ?>

					<!-- force send DEVELOPER ONLY -->
					<?php if($core->user->is_admin && false): ?>
					<a target="_blank" rel="tooltip" href="http://<?php echo $_SERVER["HTTP_HOST"] ?>/bmm/inc/send_email.php?api=<?php echo $core->settings->sender_api_key ?>"  class="btn btn-inverse	 text-white font-10" href="?mod=utilizadores&amp;view=utilizadores"><i class="icon-white icon-exclamation-sign"></i> For&ccedil;ar envio</a>
				<?php endif; ?>

				<!-- check bounces DEVELOPER ONLY -->
				<?php if($core->user->is_admin  && false): ?>
				<a target="_blank" rel="tooltip" href="http://<?php echo $_SERVER["HTTP_HOST"] ?>/bmm/inc/check_bounces.php" class="btn btn-inverse text-white font-10" href="?mod=utilizadores&amp;view=utilizadores"><i class="icon-white icon-exclamation-sign"></i> 	Bounces</a>
			<?php endif; ?>
			<a class="btn btn-danger text-white font-10" href='index.php?mod=login&view=logout'><i class="icon-white icon-off"></i> <?php echo _('Logout');?></a>
		</div>
	</div>
</div>
<div class="clear"></div>
<div class="navbar">
	<div class="navbar-inner">
		<ul class="nav">
			<li <?php if ( $_GET['mod']=='dashboard' && !isset($_GET['view'])) echo 'class="active"'; ?>><a href="?mod=dashboard"><?php echo _('Dashboard');?></a></li>
			<li <?php if ( $_GET['mod']=='grupos' && $_GET['view']=='grupos') echo 'class="active"'; ?>><a href="?mod=grupos&amp;view=grupos">Grupos</a></li>
			<li <?php if ( $_GET['mod']=='subscribers' && !isset($_GET['view'])) echo 'class="active"'; ?>><a href="?mod=subscribers"><?php echo _('Subscritores');?></a></li>
			<li <?php if ( $_GET['mod']=='newsletter' && $_GET['view']=='messages') echo 'class="active"'; ?>><a href="?mod=newsletters&amp;view=messages">Newsletters</a></li>
			<li <?php if ( $_GET['mod']=='statistics' && $_GET['view']=='statistics') echo 'class="active"'; ?>><a href="?mod=statistics&amp;view=statistics">Estat&iacute;sticas</a></li>						
			<?php if($core->user->is_admin): ?>
			<li <?php if ( $_GET['mod']=='utilizadores' && $_GET['view']=='utilizadores') echo 'class="active"'; ?>><a href="?mod=utilizadores&amp;view=utilizadores">Utilizadores</a></li>
		<?php endif; ?>
	</ul>
</div>
</div>
</div>
	<?php endif ?>

<div class="clear"></div>
<div class="content">
	<?php echo $core->output; ?>
</div>
<div id="multiple-actions-confirmation" class="modal hide fade in">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Por favor confirme a acção</h3>
	</div>
	<div class="modal-body">
		<p>Tem a certeza que pretende proceder?</p>
	</div>
	<div class="modal-footer">
		<button class="btn btn-inverse" data-dismiss="modal">Cancelar</button>
		<button class="btn btn-danger" id="confirm">Confirmar</button>
	</div>
</div>
</div>
<div class="footer">
	<div class="row-fluid">
	<div class="span12">
		Powered by
		<a class="bright-link" href="http://www.bright.pt/" target="_blank">Bright</a>
		&amp;
		<a class="digidoc-link" href="http://digidoc.pt/" target="_blank">Digidoc</a>
	</div>
	</div>
</div>
</div>

<div id="messages">
	<?php echo Tools::notify_list() ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="../inc/libs/jquery-ui/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="../inc/js/tablesort.js"></script>
<script type="text/javascript" src="../inc/js/tablesorter.pager.js"></script>
<script type="text/javascript" src="../inc/js/visualize.jQuery.js"></script>
<script type="text/javascript" src="../inc/js/jquery.dataTables.min.js"></script>
<!-- noty -->
<script type="text/javascript" src="../inc/js/noty/packaged/jquery.noty.packaged.min.js"></script>
<script type="text/javascript" src="../inc/js/noty/themes/default.js"></script>
<script type="text/javascript" src="../inc/js/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../inc/js/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript">
	CKEDITOR_PARENT = '../inc/libs/';
	CKEDITOR_BASEPATH = '../inc/libs/ckeditor/';
	CKFINDER_BASEPATH = '../inc/libs/kcfinder/browse.php?type=images';
	CKFINDER_BASEPATH_FILE = '../inc/libs/kcfinder/browse.php';
	BASEPATH = '../';
	BASEURL = '../';
</script>
<script type="text/javascript" src="../inc/libs/ckeditor/ckeditor.js"></script>
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