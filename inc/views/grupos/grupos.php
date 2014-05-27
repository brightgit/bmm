	<?php
	if(isset($_GET["remove"]))
		$grupos->remove_grupo($_GET["remove"]);

	/* Passado para o mod
	if(isset($_GET["id"])):
		$grupos->show_grupo($_GET["id"]);

	else:	Também foi removido o endif do fim
	*/
		?>

	<div class="categorias_add">

		<div style="margin-top:30px; display:none;" id="categoria_form">
			<form action="?mod=grupos&amp;view=add_grupo" method="post">
				<div>
					<label for="categoria_nome">Nome do grupo</label>
					<input type="text" name="categoria_nome" id="categoria_nome" placeholder="Nome do grupo" required="required" maxlength="500" />
				</div>
				<div style="margin-top:10px;">
					<label>É adicionado por defeito:</label>
					<div>
						<input style="display:inline-block;" type="radio" name="defeito" value="1" id="defeito_sim" palceholder="lalallala" />
						<label style="display:inline-block;" for="defeito_sim">Sim</label>
					</div>
					<div>
						<input style="display:inline-block;" type="radio" name="defeito" checked="checked" value="0" id="defeito_nao" palceholder="lalallala" />
						<label style="display:inline-block;" for="defeito_nao">Não</label>
					</div>
				</div>
				<div style="margin-top:10px;">
					<label>Adicionar todos os subscritores a este grupo:</label>
					<div>
						<input style="display:inline-block;" type="radio" name="add_subs" value="1" id="add_subs_sim" palceholder="lalallala" />
						<label style="display:inline-block;" for="add_subs_sim">Sim</label>
					</div>
					<div>
						<input style="display:inline-block;" type="radio" name="add_subs" checked="checked" value="0" id="add_subs_nao" palceholder="lalallala" />
						<label style="display:inline-block;" for="add_subs_nao">Não</label>
					</div>
				</div>
				<div>
					<br />
					<input class="btn btn-primary" type="submit" name="submit_add_categoria" value="Adicionar grupo" />
				</div>
			</form>
		</div>

	</div>


	<h1>Grupos</h1>

	<p class="btn btn-success" onclick="$('#categoria_form').toggle();"><i class="icon-white icon-plus-sign"></i> Adicionar grupo</p>
	<br />
	<br />

<?php 

if($_SESSION["user"]->is_admin)
	$query ="SELECT *, (SELECT COUNT(DISTINCT(id_subscriber)) FROM subscriber_by_cat sc WHERE sc.id_categoria = nc.id) AS total FROM `newsletter_categorias` nc;";
else
	$query = "SELECT nc.*, up.*, (SELECT COUNT(DISTINCT(id_subscriber)) FROM subscriber_by_cat sc WHERE sc.id_categoria = nc.id) AS total FROM `newsletter_categorias` nc INNER JOIN user_permissions up ON up.group_id = nc.id WHERE user_id = {$_SESSION["user"]->id}";

$res = mysql_query( $query ) or die( mysql_error() );
$i = 0;
?>

<?php if(mysql_num_rows($res) > 0): ?>

	<table id="datasort" class="table">
		<thead>
			<th>Grupo</th>
			<th>Data de Cria&ccedil;&atilde;o</th>
			<th>Total de subscritores</th>
			<th>A&ccedil;&otilde;es</th>
		</thead>
		<tbody>

			<?php while( $row = mysql_fetch_object($res) ): $total += $row->total; ?>
			<tr>
				<td><a href="?mod=grupos&amp;view=edit&amp;id=<?php echo $row->id ?>"><?php echo $row->categoria ?></a></td>
				<td><?php echo $row->date_created ?></td>
				<td><?php echo $row->total ?></td>
				<td><a class="btn btn-danger" href="?mod=grupos&amp;view=remover&amp;id=<?php echo $row->id?>"><i class="icon-white icon-remove"></i> Remover</a></td>
			</tr>
		<?php endwhile; ?>
	</tbody>
</table>

<?php else: ?>
	
	<div class="alert alert-info">N&atilde;o existem grupos definidos ou n&atilde;o tem permiss&atilde;o para os visualizar.</div>
	
<?php endif; ?>
