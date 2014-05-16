<?php

require_once('Core.php');
date_default_timezone_set("Europe/Lisbon");


//TODO: Start the session or include core
$core = new Core('bo');

if($_GET["act"] == "update_subscriber_group"):

	if(isset($_GET["id"]) && !empty($_GET["id"])){
		$subscriber_id = $_GET["id"];
		$groups = $_GET["group"];
		
		$sql = "DELETE FROM subscriber_by_cat WHERE id_subscriber = " . $subscriber_id;
		$query = mysql_query($sql);
		
		foreach($groups as $group){
			$sql = "INSERT INTO subscriber_by_cat (id_subscriber, id_categoria) VALUES(".$subscriber_id.", ".$group.")";
			mysql_query($sql);
		}
		
		echo "Atribuições alteradas com sucesso";
	}	
	
endif;

//retorna os detalhes do subscritor
if($_GET['act'] == 'get_subscriber_details' ):
	
	//buscar os grupos no qual está inserido
	$id = $_GET["id"];
	
	//buscar info
	$sql = "SELECT id, email, is_active, date_created, hard_bounces_count, data_nascimento, telefone_1, telefone_2, sexo, nome FROM subscribers WHERE id = " . $id;
	$query = mysql_query($sql);
	$info = mysql_fetch_object($query);
	
	$sql = "SELECT nc.id, nc.categoria FROM subscriber_by_cat sbc LEFT JOIN newsletter_categorias nc ON nc.`id` = sbc.id_categoria WHERE id_subscriber = " . $id;
	$query = mysql_query($sql);
	
	if($query){
		while($row = mysql_fetch_object($query)){
			$groups[] = $row;
			$groups_ids[$row->id] = true;
		}		
	}
	
	//buscar os restantes grupos
	$sql = "SELECT id, categoria FROM newsletter_categorias";
	$query = mysql_query($sql);
	
	if($query){
		while($row = mysql_fetch_object($query)){
			$categories[] = $row;
			}
	}
			
	//iniciar output
	echo '<input type="hidden" name="email_id" val="'.$id.'" />';

	if(!empty($info->nome)){
	echo '<span class="popover-label">Nome:</span>';
	echo '<span class="popover-value">'.$info->nome.' (<a href="index.php?mod=subscribers&id='.$info->id.'">ver perfil</a>)</span>';	
	}

	echo '<span class="popover-label">Email:</span>';
	echo '<span class="popover-value">'.$info->email.'</span>';

	echo '<span class="popover-label">Data de inserção:</span>';
	echo '<span class="popover-value">'.date("d/m/Y", strtotime($info->date_created)).'</span>';
	
	echo '<span class="popover-label">Emails devolvidos (bounces):</span>';
	echo '<span class="popover-value">'.$info->hard_bounces_count.'</span>';

	echo '<span class="popover-label">Pertence a:</span>';
	echo '<ul class="popover-groups-list">';	
	foreach($groups as $group){
		echo '<li class="group">'.$group->categoria.'</li>';
	}
	echo '</ul>';
	
	echo '<span class="popover-label">Atribuir a outros grupos</span>';
	echo '<select name="ajax-subscriber-groups" multiple="multiple" class="popover-all-groups-list">';
	foreach($categories as $category){
		$selected = array_key_exists($category->id, $groups_ids) ? "selected='selected'":"";
		echo '<option '.$selected.' value="'.$category->id.'">'.$category->categoria.'</option>';
	}
	echo '</select>';
	
	echo "<div class=\"ajax-response\"></div>";
	
	echo '<div class="ajax-button-go"><button class="btn btn-small btn-primary">Confirmar</button></div>';


endif;

if($_GET['act'] == 'save_active' ):
	$active = $core->getTools()->getPost('value');
	$id = $core->getTools()->getPost('id');
	$db = $core->getTools()->getPost('field');
	if(strtolower($active)=='activo')
		$active = 1;
	else
		$active = 0;
	$sql="update $db set is_active = $active where id = $id and lang = '{$_SESSION['lang']}'";
	mysql_query($sql);
endif;
if($_GET['act'] == 'save_active_menu' ):
	$active = $core->getTools()->getPost('value');
	$id = $core->getTools()->getPost('id');
	if(strtolower($active)=='activo')
		$active = 1;
	else
		$active = 0;
	$sql="update sub_menus set is_active = $active where id = $id and lang = '{$_SESSION['lang']}'";
	mysql_query($sql);
endif;
if($_GET['act'] == 'save_name' ):
	$name = $core->getTools()->getPost('value');
	$id = $core->getTools()->getPost('id');

	$sql="update menus set nome = '$name' where id = $id and lang = '{$_SESSION['lang']}'";
	mysql_query($sql);
endif;
if($_GET['act'] == 'save_name_menu' ):
	$name = $core->getTools()->getPost('value');
	$id = $core->getTools()->getPost('id');

	$sql="update sub_menus set name = '$name' where id = $id and lang = '{$_SESSION['lang']}'";
	echo $sql;
	mysql_query($sql);
endif;
if($_GET['act'] == 'new_menu' ):
	$name = $core->getTools()->getPost('nome');
	$state = $core->getTools()->getPost('state');

	$sql="insert into menus (nome_pt,is_active) values('$name',$state)";
	if(mysql_query($sql))
		echo 1;
	else
		echo 0;
endif;
if($_GET['act'] == 'ordering' ):
	$counter = 1;
	$data = $_POST['myJson'];
	for ($i=0; $i < sizeof($data); $i++) {
		$row = $data[$i][0];
		if(is_nan($row[0]) || is_nan($row[1])){
			echo _("ERRO: Não foi possível gravar a ordenação dos menus. Por favor tente de novo");
			return FALSE;
		}
		else{
			$pid = $row[1];
			$id = $row[0];
			$sql = "update sub_menus set sort_order = $counter, parent_id = $pid
					where 	lang = '{$_SESSION['lang']}' and
							id = $id";
			mysql_query($sql);
		}
		$counter += 1;
	}
endif;
if($_GET['act'] == 'update_module' ){
	$tools = $core->getTools();
	$id = $tools->getPost('mod');
	$module = new Modules();
	$res = $module->getModulesPagesByMod($id);
	if(!$res){
		echo _('Erro: Não foi possível criar a lista de módulos');
	}else{
		echo '<select name="module_id"  id="module_id">
				<option value="-1">'._('Escolha/Crie Página').'</option>
				<option value="0">'._('Criar Novo').'</option>';
		while ($row = mysql_fetch_object($res)) {
			if(!is_null($row->checked))
				$checked = 'selected="selected"';
			echo '<option value="'.$row->id.'" '.$checked.'>'.$row->title.'</option>';
			$checked = '';
		}
		echo '</select>';
	}
}
if($_GET['act'] == 'update_page' ){
	$tools = $core->getTools();
	$id = $tools->getPost('page');
	$mod = $tools->getPost('mod');
	if(is_numeric($id) && $id != -1){
		$module = new Modules();
		$module_name = $module->getModulesMod($mod);
		$core->getMod($module_name,Array('id' => $id, 'form' => 1));
	}
}
if($_GET['act'] == 'remove-menu-node' ){
	$tools = $core->getTools();
	$id = $tools->getPost('id');
	if(is_numeric($id)){
		$sql = "delete from sub_menus where id = $id and lang = '{$_SESSION['lang']}'";
		echo $sql;
		mysql_query($sql);
	}
}
if($_GET['act'] == 'change-menu-status' ){
	$tools = $core->getTools();
	$id = $tools->getPost('id');
	$newstatus = $tools->getPost('nstat');
	if(is_numeric($id)){
		$sql = "update sub_menus set is_active = $newstatus where id = $id and lang = '{$_SESSION['lang']}'";
		mysql_query($sql);
	}
}
if($_GET['act'] == 'save_gallery_sort' ){
	$tools = $core->getTools();
	$items = $_POST['items'];
	foreach($items as $item=>$val){
		if(is_numeric($item) && is_numeric($val)){
			$sql = "UPDATE galleries SET `sort_order` = {$item} WHERE `id` = {$val} and lang = '{$_SESSION['lang']}' ";
			echo $sql;
			$query = mysql_query($sql);
		}
	}
}
if($_GET['act'] == 'save_testimony_sort' ){
	$tools = $core->getTools();
	$items = $_POST['items'];
	foreach($items as $item=>$val){
		if(is_numeric($item) && is_numeric($val)){
			$sql = "UPDATE testimonies SET `sort_order` = {$item} WHERE `id` = {$val} and lang = '{$_SESSION['lang']}' ";
			$query = mysql_query($sql);
		}
	}
}
if($_GET['act'] == 'save_slides_sort' ){
	$tools = $core->getTools();
	$items = $_POST['items'];
	foreach($items as $item=>$val){
		if(is_numeric($item) && is_numeric($val)){
			$sql = "UPDATE slides SET `sort_order` = {$item} WHERE `id` = {$val}";
			$query = mysql_query($sql);
		}
	}
}

//destaques
if($_GET["act"] == "sort_destaques"){
	$sort_order = 0;
	while(isset($_GET["destaques"][$sort_order])){
		if($_GET['destaques'][$sort_order] =='null'){
			$_GET['destaques'][$sort_order] = 0;
		}
		$query = "UPDATE `destaques` SET `sort_order`='".$sort_order."' WHERE `id`='".$_GET['destaques'][$sort_order]."'";
		//echo $query.'<br />';
		mysql_query($query) or die(mysql_error());
		$sort_order++;
	}
}

if($_GET["act"] == "get_subscribers"){

	header('Content-type: application/json');

	if($_GET['sSortDir_0']=='asc'){
		$dir = 'ASC';
	}else{
		$dir = 'DESC';
	}

	if($_GET['iSortCol_0']==1){
		$order = " ORDER BY `id` ".$dir;
	}elseif($_GET['iSortCol_0']==2){
		$order = " ORDER BY `nome` ".$dir;
	}elseif($_GET['iSortCol_0']==3){
		$order = " ORDER BY `email` ".$dir;
	}elseif($_GET['iSortCol_0']==4){
		$order = " ORDER BY `hard_bounces_count` ".$dir;
	}elseif($_GET['iSortCol_0']==5){
		$order = " ORDER BY `is_active` ".$dir;
	}else{
		$order = " ORDER BY s.`id` ".$dir;
	}

	//$sql = "select s.* from subscribers s";
	$sql = "SELECT s.id, s.email, s.is_active, s.hard_bounces_count FROM subscriber_by_cat sbc ";

	if(isset($_GET["group_id"])){
		//$sql .= " inner join subscriber_by_cat sbc ON s.id = sbc.id_subscriber where sbc.id_categoria = {$_GET["group_id"]}";
		$sql .= " LEFT JOIN subscribers s ON s.id = sbc.id_subscriber WHERE sbc.id_categoria = {$_GET["group_id"]}";
	}		

	if($_GET['sSearch']!=''){
		$sql_part[0] = "nome LIKE '".$_GET['sSearch']."' OR nome LIKE '%".$_GET['sSearch']."' OR nome LIKE '".$_GET['sSearch']."%' OR nome LIKE '%".$_GET['sSearch']."%'";
		$sql_part[1] = "email LIKE '".$_GET['sSearch']."' OR email LIKE '%".$_GET['sSearch']."' OR email LIKE '".$_GET['sSearch']."%' OR email LIKE '%".$_GET['sSearch']."%'";


		$sql .= " AND (" . implode(" OR ", $sql_part).') GROUP BY s.id';
	}else{
		$sql .= "";
		$sql .= " GROUP BY s.email ";
	}

	$sql .= $order;

	$sql_count = $sql;
	
	if($_GET['iDisplayLength'] == -1)
		$sql .= "";
	else
		$sql .= " LIMIT ".$_GET['iDisplayStart'].", ".$_GET['iDisplayLength'];

//		echo '<hr />'.$sql.'<hr />';
	$query = mysql_query($sql) or die(mysql_error());

	$i = 0;

	if($query)
		while($row = mysql_fetch_object($query)){

			if($row->is_active){
				$active = '<img src="../inc/img/admin/yes.png" alt="Sim" />';
			}
			else{
				$active = '<img src="../inc/img/admin/no.png" alt="N&atilde;o" />';
			}

			$output[$i][0] = "<input type=\"checkbox\" name=\"items[]\" value=\"".$row->id."\" />";
			$output[$i][1] = $row->id;
			//$output[$i][2] = $row->nome;
			$output[$i][2] = "<a href=\"\" class=\"popup-ajax\" data-toggle=\"popover\" data-id=\"".$row->id."\">" . $row->email . "</span>";
			$output[$i][3] = $row->hard_bounces_count;
			$output[$i][4] = $active;
			
			$query_2 = "SELECT id, categoria FROM `newsletter_categorias`";
			//$res = mysql_query( $query_2 ) or die(mysql_error());
			$output[$i][5] = '';
			while( $row2 = mysql_fetch_array( $res ) ) { 

				if($_SESSION["user"]->is_admin)
					$query_3 = "SELECT * FROM `subscriber_by_cat` WHERE `id_subscriber` = '".$row->id."' AND `id_categoria` = '".$row2['id']."' LIMIT 1";
				else
					$query_3 = "SELECT * FROM `subscriber_by_cat` sbc INNER JOIN user_permissions up ON up.group_id = sbc.id_categoria WHERE sbc.`id_subscriber` = ".$row->id." AND sbc.`id_categoria` = ".$row2["id"]." AND up.user_id = ".$_SESSION["user"]->id." LIMIT 1";

				echo $query_3 . "<hr /><br />";

				//$res2 = mysql_query( $query_3 ) or die( mysql_error() );
			
				if( mysql_num_rows($res2) != 0 ) {
					$output[$i][5] .= '<a class="label label-success" href="Javascript:void(0);" onclick="return toogle_newsletter_categoria('.$row2['id'].', '.$row->id.', this);">'.$row2['categoria'].'</a> ';
				}else{
					if($_SESSION["user"]->is_admin)
						$output[$i][5] .= '<a class="label" href="Javascript:void(0);" onclick="return toogle_newsletter_categoria('.$row2['id'].', '.$row->id.', this);">'.$row2['categoria'].'</a> ';
					//um user sem autorizacao nunca verá o grupo para o qual não tem acesso
				}
			}
			

			$i++ ;
		}


		$query = mysql_query($sql_count);

		if($query)
			$total_records = mysql_num_rows($query);
		else
			$total_records = 0;

		if	( !isset($output) ) {
		$output = FALSE;
		}
	$obj->aaData = $output;
	$obj->sEcho = $_GET['sEcho']+1;
	$obj->iTotalRecords = $total_records;
	$obj->iTotalDisplayRecords = $total_records;
	echo json_encode($obj);

}

if($_GET["act"] == "get_exclusions"){

	header('Content-type: application/json');

	if($_GET['sSortDir_0']=='asc'){
		$dir = 'ASC';
	}else{
		$dir = 'DESC';
	}

	if($_GET['iSortCol_0']==1){
		$order = " ORDER BY `id` ".$dir;
	}elseif($_GET['iSortCol_0']==2){
		$order = " ORDER BY `nome` ".$dir;
	}elseif($_GET['iSortCol_0']==3){
		$order = " ORDER BY `email` ".$dir;
	}elseif($_GET['iSortCol_0']==4){
		$order = " ORDER BY `hard_bounces_count` ".$dir;
	}elseif($_GET['iSortCol_0']==5){
		$order = " ORDER BY `is_active` ".$dir;
	}else{
		$order = " ORDER BY s.`id` ".$dir;
	}

	$sql = "select s.* from subscribers s";

	if(isset($_GET["group_id"]))
		$sql .= " where s.is_active = 0";

	if($_GET['sSearch']!=''){
		$sql_part[0] = "(nome LIKE '".$_GET['sSearch']."' OR nome LIKE '%".$_GET['sSearch']."' OR nome LIKE '".$_GET['sSearch']."%' OR nome LIKE '%".$_GET['sSearch']."%')";
		$sql_part[1] = "(email LIKE '".$_GET['sSearch']."' OR email LIKE '%".$_GET['sSearch']."' OR email LIKE '".$_GET['sSearch']."%' OR email LIKE '%".$_GET['sSearch']."%')";


		$sql .= " AND " . implode(" OR ", $sql_part).' GROUP BY s.id';
	}else{
		$sql .= "";
	}

	$sql .= $order;

	$sql_count = $sql;
	
	if($_GET['iDisplayLength'] == -1)
		$sql .= "";
	else
		$sql .= " LIMIT ".$_GET['iDisplayStart'].", ".$_GET['iDisplayLength'];

	//echo '<hr />'.$sql.'<hr />';
	$query = mysql_query($sql) or die(mysql_error());

	$i = 0;

	if($query)
		while($row = mysql_fetch_object($query)){

			if($row->is_active){
				$active = '<img src="../inc/img/admin/yes.png" alt="Sim" />';
			}
			else{
				$active = '<img src="../inc/img/admin/no.png" alt="N&atilde;o" />';
			}

			//$output[$i][0] = "<input type=\"checkbox\" name=\"items[]\" value=\"".$row->id."\" />";
			//$output[$i][2] = $row->nome;

			$output[$i][0] = $row->id;
			$output[$i][1] = $row->email;
			$output[$i][2] = $row->hard_bounces_count;
			$output[$i][3] = $active;
					

			$i++ ;
		}


		$query = mysql_query($sql_count);

		if($query)
			$total_records = mysql_num_rows($query);
		else
			$total_records = 0;

		if	( !isset($output) ) {
		$output = FALSE;
		}
	$obj->aaData = $output;
	$obj->sEcho = $_GET['sEcho']+1;
	$obj->iTotalRecords = $total_records;
	$obj->iTotalDisplayRecords = $total_records;
	echo json_encode($obj);

}

if($_GET['act'] == 'contactform'){

	$name = tools::getGet('name');
	$email = tools::getGet('email');
	$message = tools::getGet('message');
	$to = 'geral@bvcacilhas.pt';
	$subject = 'bvcacilas.pt - Novo contacto';
	$msg = 'Recebida nova mensagem de <b>'.$email.'</b>: <br /><p>'.$message.'</p>';

	//validar form basic
	if(!empty($name) || !empty($email) || !empty($message))
		$sendmail = @tools::send_email($msg, $to, $subject);
	else
		$sendmail = false;

	echo $sendmail == true ? 'Mensagem enviada com sucesso.' : 'N&atilde;o foi poss&iacute;vel enviar a mensagem. Em alternativa, sugerimos que utilize o endere&ccedil;o <a href="mailto:geral@bvcacilhas.pt">geral@bvcacilhas.pt</a>';
}
if($_GET['act'] == 'recrutamentoform'){

	$name = tools::getGet('name');
	$birthdate = tools::getGet('birthdate');
	$address = tools::getGet('address');
	$phone = tools::getGet('phone');
	$profession = tools::getGet('profession');	
	$message = tools::getGet('message');
	
	$to = 'recrutamento@bvcacilhas.pt';
	$subject = 'bvcacilas.pt - Novo pedido de admissão';
	$msg = 'Recebida nova mensagem de <b>'.$name.'</b>: <br /><br />
		<table style="font-size:12px">
			<tr>
				<td style="width: 40%;"><b>Nome</b>:</td>
				<td>'.$name.'</td>				
			</tr>
			<tr>
				<td><b>Data de nascimento</b>:</td>
				<td>'.$birthdate.'</td>				
			</tr>
			<tr>
				<td><b>Morada</b>:</td>
				<td>'.$address.'</td>				
			</tr>
			<tr>
				<td><b>Telefone</b>:</td>
				<td>'.$phone.'</td>				
			</tr>
			<tr>
				<td><b>Profiss&atilde;o</b>:</td>
				<td>'.$profession.'</td>				
			</tr>
			<tr>
				<td><b>Mensagem</b>:</td>
				<td>'.$message.'</td>				
			</tr>
			
		</table><br /><br />';

	//validar form basic
	if(!empty($name) && !empty($birthdate) && !empty($address) && !empty($phone) && !empty($profession))
		$sendmail = @tools::send_email($msg, $to, $subject);
	else
		$sendmail = false;

	echo $sendmail == true ? 'Mensagem enviada com sucesso.' : 'N&atilde;o foi poss&iacute;vel enviar a mensagem. Em alternativa, sugerimos que utilize o endere&ccedil;o <a href="mailto:geral@bvcacilhas.pt">recrutamento@bvcacilhas.pt</a>';
}
if($_GET['act'] == 'faqs_sort' ):
	$order = explode("_", $_POST['order']);
	$i = 1;
	while(isset($order[$i])){
		$query = "UPDATE `faqs` SET `sort_order`='".$i."' WHERE `id`='".$order[$i]."'";
		mysql_query($query);
		$i++;
	}
endif;
if($_GET['act'] == 'imprensa_sort' ):
	$order = explode("_", $_POST['order']);
	$i = 1;
	while(isset($order[$i])){
		$query = "UPDATE `media` SET `sort_order`='".$i."' WHERE `id`='".$order[$i]."'";
		echo $query;
		mysql_query($query);
		$i++;
	}
endif;
if($_GET['act'] == 'images_sort' ):
	$order = explode("_", $_POST['order']);
	$i = 1;
	while(isset($order[$i])){
		$query = "UPDATE `media` SET `sort_order`='".$i."' WHERE `id`='".$order[$i]."'";
		echo $query;
		mysql_query($query);
		$i++;
	}
endif;
if($_GET['act'] == 'videos_sort' ):
	$order = explode("_", $_POST['order']);
	$i = 1;
	while(isset($order[$i])){
		$query = "UPDATE `media` SET `sort_order`='".$i."' WHERE `id`='".$order[$i]."'";
		echo $query;
		mysql_query($query);
		$i++;
	}
endif;
if($_GET['act'] == 'faqs_toogle' ):
	$query = "SELECT * FROM `faqs` WHERE `id`='".$_POST['id']."' LIMIT 1";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);

	if($row['active']==1){
		$query = "UPDATE `faqs` SET `active`='0' WHERE `id`='".$row['id']."'";
		echo 'inativo';
	}else{
		$query = "UPDATE `faqs` SET `active`='1' WHERE `id`='".$row['id']."'";
		echo 'ativo';
	}
	mysql_query($query) or die(mysql_error());
endif;
if($_GET['act'] == 'media_toogle' ):
	$query = "SELECT * FROM `media` WHERE `id`='".$_POST['id']."' LIMIT 1";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);

	if($row['is_active']==1){
		$query = "UPDATE `media` SET `is_active`='0' WHERE `id`='".$row['id']."'";
		echo 'inativo';
	}else{
		$query = "UPDATE `media` SET `is_active`='1' WHERE `id`='".$row['id']."'";
		echo 'ativo';
	}
	mysql_query($query) or die(mysql_error());
endif;
if($_GET['act'] == 'cart_toogle' ):
	$query = "SELECT * FROM `products` WHERE `id`='".$_POST['id']."' LIMIT 1";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);

	if($row['is_active']==1){
		$query = "UPDATE `products` SET `is_active`='0' WHERE `id`='".$row['id']."'";
		echo 'inativo';
	}else{
		$query = "UPDATE `products` SET `is_active`='1' WHERE `id`='".$row['id']."'";
		echo 'ativo';
	}
	mysql_query($query) or die(mysql_error());
endif;
if($_GET['act'] == 'font_size' ):
	$_SESSION['font_size'] = $_GET['font'];
endif;

if($_GET['act']=='sort_faqs'){
	$keys = array_keys($_GET['faqs']);
	$sort_order = 0;
	while(isset($keys[$sort_order])){
		if($_GET['faqs'][$keys[$sort_order]]=='null'){
			$_GET['faqs'][$keys[$sort_order]] = 0;
		}
		$query = "UPDATE `categorias_faqs` SET `sort_order`='".$sort_order."', `parent`='".$_GET['faqs'][$keys[$sort_order]]."' WHERE `id`='".$keys[$sort_order]."'";
		echo $query.'<br />';
		mysql_query($query) or die(mysql_error());
		$sort_order++;
	}
}
if($_GET['act']=='sort_media'){
	$keys = array_keys($_GET['media']);
	$sort_order = 0;
	while(isset($keys[$sort_order])){
		if($_GET['media'][$keys[$sort_order]]=='null'){
			$_GET['media'][$keys[$sort_order]] = 0;
		}
		$query = "UPDATE `categorias_media` SET `sort_order`='".$sort_order."', `parent`='".$_GET['media'][$keys[$sort_order]]."' WHERE `id`='".$keys[$sort_order]."'";
		echo $query.'<br />';
		mysql_query($query) or die(mysql_error());
		$sort_order++;
	}
}
if($_GET['act']=='toogle_news_cat'){

	$query = "SELECT * FROM `subscriber_by_cat` WHERE `id_subscriber` = '".$_GET['subscriber_id']."' AND  `id_categoria` = '".$_GET['categoria_id']."'";

	$res = mysql_query( $query ) or die( mysql_error() );

	$val = mysql_num_rows($res);
	if( $val == 0 ){
	

		$query = "INSERT INTO `subscriber_by_cat` (`id_subscriber`, `id_categoria`) VALUES ('".$_GET['subscriber_id']."', '".$_GET['categoria_id']."')";
		mysql_query( $query ) or die( mysql_error() );
		die('true');
		
	}else{
	
		$query = "DELETE FROM `subscriber_by_cat` WHERE `id_subscriber` = '".$_GET['subscriber_id']."' AND  `id_categoria` = '".$_GET['categoria_id']."'";
		mysql_query( $query ) or die(mysql_error());
		die('false');
		
	}

}
if($_GET['act']=='sort_media_items'){
	$id = $_GET['id'];
	$i=0;
	while(isset($id[$i])){
		$query = "UPDATE `media` SET `sort_order`='".$i."' WHERE `id`='".$id[$i]."'";
		mysql_query($query) or die(mysql_error());
		$i++;
	}
}

$core->__destruct();

?>
