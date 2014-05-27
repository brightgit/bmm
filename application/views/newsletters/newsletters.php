	<?php
	/* Removed and passed to mod.
	if(isset($_GET["remove"]))
		$this->remove_message($_GET["remove"]);

	if(isset($_GET["duplicate"]))
		$this->duplicate_message($_GET["duplicate"]);
	*/
	?>
	<div class="main_div">
		<h1>Newsletters</h1>
		<a href="?mod=newsletters&view=add_mensagem" class="add-new-news btn btn-success"><i class="icon-white icon-plus-sign"></i> <?php echo _('Adicionar newsletter');?></a>

		<div class="clear"></div>

		<br />

		<?php
		if(!isset($_GET['start'])){
			$start = 0;
		}else{
			$start = $_GET['start'];
		}
		$num_mensagens = $newsletters->get_num_mensagens();
		if($start != 0){
			?>
			<a href="?mod=newsletters&view=messages&start=<?php echo ($start-30); ?>">Página anterior</a>
			<?php
		}
		if($num_mensagens >= ($start+30)){
			?>
			<a href="?mod=newsletters&view=messages&start=<?php echo ($start+30); ?>">Página seguinte</a>
			<?php
		}
		?>
		<table class="addpadding table" id="datasort">
			<thead>
				<tr>
					<th>#</th>
					<th>Assunto</th>
					<th>Data Criação</th>
					<th># Envios</th>
					<th class="nosort">A&ccedil;&otilde;es</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$num_mensagens = $newsletters->get_num_mensagens();
				$mensagens_res = $newsletters->get_messages();
				if(mysql_num_rows($mensagens_res) < 1){
					?>
					<tr>
						<td colspan="5"><div class="alert alert-info">N&atilde;o est&atilde;o criadas newsletters ou n&atilde;o tem permiss&atilde;o para as visualizar</div></td>
					</tr>
					<?php
				}else{   
					while($row = mysql_fetch_array($mensagens_res)){
						?>
						<tr>
							<td><?php echo $row['id']; ?></td>
							<td class="alignleft"><a href="?mod=newsletters&amp;view=add_mensagem&amp;id=<?php echo $row['id']?>"><?php echo $row['assunto']; ?></td>
							<td><?php echo $row['data_criada']; ?></td>
							<td><?php echo $row["total_envios"]; ?></td>
							<td>
								<div class="table-actions">
									<a rel="tooltip-top" data-original-title="Editar" class="btn" href="?mod=newsletters&amp;view=add_mensagem&amp;id=<?php echo $row['id']?>"><i class="icon icon-pencil"></i></a>
									<a rel="tooltip-top" data-original-title="Estat&iacute;sticas" href="?mod=statistics&amp;view=newsletter_statistics&amp;id=<?php echo $row['id']; ?>" class="btn"><i class="icon icon-align-left"></i></a>
									<a rel="tooltip-top" data-original-title="Replicar" class="btn" href="?mod=newsletters&amp;view=replicar&amp;duplicate=<?php echo $row["id"] ?>"><i class="icon icon-resize-full"></i></a>
									<a rel="tooltip-top" data-original-title="Remover" class="btn btn-danger" href="?mod=newsletters&amp;view=remover&amp;remove=<?php echo $row["id"] ?>"><i class="icon-white icon-remove"></i></a>
									<a rel="tooltip-top" data-original-title="Preparar envio" class="btn btn-primary" href="?mod=send&amp;view=pre_send&amp;id=<?php echo $row['id']; ?>"><i class="icon-white icon-share"></i></a>
								</div>
							</td>
						</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>

		</div>
