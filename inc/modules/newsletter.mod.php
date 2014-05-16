<?php

class Newsletter {

	//private $mode = 'fe';
	private $debug = '';
	public $lang;
	private $mod;

	function __construct($mode = '') {
		$this->debug = new Debug();
		$this->lang = $_SESSION['lang'];
		$this->mod = 'slider';
	}

	function __destruct() {
		$this->debug->__destruct();
		unset($this->debug);
		$this->lang = null;
		unset($this->lang);
	}

	function get_send_test( $id ){
		$query = "SELECT * FROM `mensagens_teste_enviadas` WHERE `mensagem_id` = '".$id."' ORDER BY `hora` DESC";
		$res = mysql_query($query) or die(mysql_error());
		return $res;
	}

	/*
	 * Devolve a lista de todos sliders ou info de uma especifica (se definidoo $id)
	 */
	function insert_mensagem($mensagem){

		//gravar a mensagem
		$query = "INSERT INTO `mensagens` (`assunto`, `url`, `mensagem`, `mensagem_browser`, `mensagem_text`, `data_update`, `estado`) VALUES ('".$mensagem->assunto."', '".$mensagem->url."', '".htmlspecialchars_decode( ( addslashes($mensagem->mensagem) ) )."', '".htmlspecialchars_decode( ( addslashes($mensagem->mensagem_browser) ) )."', '".htmlspecialchars_decode( ( $mensagem->mensagem_text ) )."', '".$mensagem->data_update."', '".$mensagem->estado."')";
		$result = mysql_query($query) or die(mysql_error().$query);

		//buscar o id da mensagem
		$sql = "SELECT MAX(id) AS id FROM mensagens";
		$result = mysql_query($sql);
		$mensagem = mysql_fetch_object($result);

		//gravar as permissoes
		$save_permissions = $this->save_newsletter_permissions($_POST["user_permissions"], $mensagem->id);

		return true;
	}



	function save_newsletter_permissions($users, $newsletter_id){


		//nesta situação, remover todas as permissões
		if(!$users){
			$sql = "DELETE FROM user_permissions_newsletter WHERE id_newsletter = {$newsletter_id}";
			$query = mysql_query($sql);
			$errors .= mysql_error();
		}

		else{
			$users_string = implode(",", $users);

			//apagar as permissões já definidas
			$sql = "DELETE FROM user_permissions_newsletter WHERE id_newsletter = {$newsletter_id}";
			$delete = mysql_query($sql);

			foreach ($users as $user_id) {
				$sql = "INSERT INTO user_permissions_newsletter (id_user, id_newsletter) VALUES ({$user_id}, {$newsletter_id})";
				$insert = mysql_query($sql);

				$errors .= mysql_error();
			}
		}

		if(empty($errors))
			echo "<div class=\"alert alert-success\">Permiss&otilde;es actualizadas com sucesso</div>";
		else
			echo "<div class=\"alert alert-danger\">Permiss&otilde;es não foram actualizadas com sucesso. <b>".$errors."</b></div>";
		
	}

	function update_mensagem($mensagem){


		//update a newsletter
		$query ="UPDATE `mensagens` SET 
		`assunto` = '".$mensagem->assunto."',
		`url` = '".$mensagem->url."',
		`mensagem` = '".htmlspecialchars_decode( htmlspecialchars_decode( (addslashes($mensagem->mensagem)) ) )."',
		`mensagem_browser` = '".htmlspecialchars_decode( htmlspecialchars_decode( (addslashes($mensagem->mensagem_browser)) ) )."',
		`mensagem_text` = '".htmlspecialchars_decode( htmlspecialchars_decode( ($mensagem->mensagem_text) ) )."',
		`data_update` = '".$mensagem->data_update."'
		WHERE `id`='".$mensagem->id."'";

		//update a permissões
		$save_permissions = $this->save_newsletter_permissions($_POST["user_permissions"], $mensagem->id);

		mysql_query($query) or die(mysql_error().$query);

		echo "<div class=\"alert alert-success\">Newsletter actualizada com sucesso</div>";
		return true;
	}
	function initialize_mensagem_from_post(){
		$return = new stdClass;
		$return->id = $_POST['mensagem_id'];
		$core = new Core();
		$tools = $core->getTools();

		$return->assunto = $tools->getPost('assunto');
		$return->mensagem = $tools->get_page('mensagem');
		$return->mensagem_browser = $tools->get_page('mensagem_browser');
		$return->mensagem_text = $tools->get_page('mensagem_text');
		$return->url = $tools->getPost('url');

		$return->data_criada = NULL;
		$return->data_update = $tools->get_timestamp();
		$return->estado = 'Não utilizada';
		return $return;
	}

	function initialize_mensagem($id = -1){
		$return = new stdClass;
		if($id == -1){
			$return->id = -1;
			$return->assunto = '';
			$return->mensagem = '';
			$return->mensagem_browser = '';
			$return->data_criada = '0000-00-00 00:00:00';
			$return->data_update = '0000-00-00 00:00:00';
			$return->estado = '';
		}else{
			if(!($return = $this->get_mensagem_by_id($id))){
				$return = $this->initialize_mensagem();
			}
		}
		return $return;
	}


	function get_num_mensagens(){
		$query = "SELECT * FROM `mensagens`";
		$res = mysql_query($query) or die(mysql_error());
		return mysql_num_rows($res);
	}
	function get_messages($start = 0, $limit = 30){

		if($_SESSION["user"]->is_admin)
			$query = "SELECT * FROM mensagens ORDER BY id DESC LIMIT ".$start.", ".$limit;
		else
			$query = "SELECT * FROM user_permissions_newsletter upn INNER JOIN mensagens m ON m.id = upn.id_newsletter where id_user = {$_SESSION["user"]->id} ORDER BY m.id DESC LIMIT ".$start.", ".$limit;

		$res = mysql_query($query) or die(mysql_error());
		return $res;
	}

	function get_mensagem_by_id($id){
		$query = "SELECT * FROM `mensagens` WHERE `id` = '".$id."'";
		$res = mysql_query($query) or die(mysql_error());
		if( mysql_num_rows($res) < 1 ){
			return false;
		}else{
			return mysql_fetch_object($res);
		}
	}
	function getSubscribers($id = 0) {
		$sql = "select * from subscribers";
		if($id!=0)
			$sql .= " where id = $id";

		$sql .= " LIMIT 100";

		$res = mysql_query($sql);
		if(!$res)
			$this->debug->dbErrors($sql);
		else
			return $res;
	}

	//info dos subscritores - estas functions poderiam ser transportadas para o repositório da bright, juntamente com os subscritores...

	function get_subscribers_in_date_interval($start, $finish){
		$query = "SELECT * FROM `subscribers` WHERE date_in BETWEEN '".$start."' AND '".$finish."'";
	}

	function get_subscribers_info_year($year){
		$this->get_subscribers_in_date_interval(1, 2);
		return true;
	}

	function save() {
		
		$core = new Core();
		$tools = $core->getTools();

		//dados a inserir
		$email = $tools->getPost('email');
		$nome = $tools->getPost('nome');
		$is_active = $tools->getPost('is_active');
		(int) $group_id = $tools->getPost("group_id");

		$sql = "INSERT into subscribers set nome = '{$nome}', email = '{$email}', is_active = {$is_active}";
		$res = mysql_query($sql);

		if ($res == true)
			echo "<div class=\"alert alert-success\">Endereço de email adicionado com sucesso &agrave; lista de subscritores</div>";
		else
			echo "<div class=\"alert alert-info\">O endere&ccedil;o de email j&aacute; se encontra na lista de subscritores</div>";
		

		//fixed: não se podia ir buscar o subscriber por id, pois nem sempre é inserido. por vezes já existe e só é necessário fazer a atribuição				
		$query = "SELECT * FROM `subscribers` WHERE email = '{$email}' LIMIT 1";
		$res = mysql_query( $query ) or die(mysql_error());
		
		$id = mysql_fetch_object( $res );

		$query = "SELECT * FROM `newsletter_categorias` WHERE `is_default` = '1'";
		$res = mysql_query( $query ) or die(mysql_error());

		//adicionar ao grupo
		$sql = "INSERT INTO `subscriber_by_cat` (`id_subscriber`, `id_categoria`) VALUES ( {$id->id}, {$group_id} )";
		$res = mysql_query($sql);

		if($res == true)
			echo "<div class=\"alert alert-success\">O endere&ccedil;o de email foi associado ao grupo seleccionado</div>";
		else
			echo "<div class=\"alert alert-warning\">O endere&ccedil;o de email j&aacute; est&aacute; associado ao grupo seleccionado</div>";
		
		while( $row = @mysql_fetch_object($res) ) {
			$query ="INSERT INTO `subscriber_by_cat` (`id_subscriber`, `id_categoria`) VALUES ('".$id->id."', '".$row->id."')";
			mysql_query( $query ) or die( mysql_error() );
		}
		
		Tools::remember_subscriber_tab($group_id);

		//@header("Location: "._ROOT."?mod=newsletter", TRUE, 302);
	}

	function add_to_exclusion(){

		$emails_list = $_POST["add_to_exclusion"];
		$emails = $this->find_emails_in_string($emails_list);
		
		if($emails)
			$this->db_add_to_exclusion($emails);
		else
			echo "<div class=\"alert alert-warning\">N&atilde;o foram encontrados e-mails v&aacute;lidos para adicionar &agrave; lista de exclus&atilde;o</div>";
	}

	function db_add_to_exclusion($emails){
		if (empty($emails)){
			echo "N&atilde;o foram enviados e-mails para adicionar &agrave; base de dados";
			return false;
		}
		else{
			
			foreach ($emails as $email) {
				$query = "INSERT INTO `subscribers` (email, is_active) VALUES (\"$email\", 0)";
				$insert = mysql_query($query);
			}

			echo "<div class=\"alert alert-success\">Foram adicionados ".count($emails)." endere&ccedil;os &agrave; lista de exclus&atilde;o</div>";
		}

	}

	function find_emails_in_string($string){
		$string = str_replace("\\", "", $string);

		$matches = array(); //create array
		$pattern = '/[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}/';
		preg_match_all($pattern, $string, $matches); //find matching pattern

		$emails = $matches[0];
		$emails = array_map("trim", $emails);
		return empty($emails) ? false : $emails;

	}


	function import_file(){
		
		ini_set("memory_limit", "-1");

		$group_id = (int) $_POST["csv_group_id"];
		$file = $_FILES["csv"]["tmp_name"];

		if(empty($file)){
			echo "<div class=\"alert alert-danger\">N&atilde;o foi seleccionado um ficheiro para importa&ccedil;&atilde;o.</div>";
			return false;
		}
		
		$string = file_get_contents($file); // Load text file contents
		$string = str_replace("\\", "", $string);

		$matches = array(); //create array
		//$pattern = '/[.A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_\.]+)/'; //regex for pattern of e-mail address
		$pattern = '/[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}/';
		preg_match_all($pattern, $string, $matches); //find matching pattern

		$i = 0; //itera pelos e-mails
		$f = 0; //itera a cada 10000 ou outro valor, para separar a inserção em multiplos queries devido à limitação do servidor

		//fgetcsv remove logo os espaços "à la TRIM()"
		foreach ($matches[0] as $email) {

			//preparar email
			$email = trim($email);
			$email = str_replace("\"", "", $email);

			//se for um e-mail válido
			if(Tools::is_email($email)){
				$valid_emails[$f][] = "('".$email."' )";
				$list_valid_emails[] = $email;
			}				
			else{
				$invalid_emails[] = "('".$email."' )";
				$list_invalid_emails[] = $email;
			}

			$i++;

			//incrementa o $f pois já existem 10000 e-mails válidos no último batch
			if($i % 10000 == 0){
				$f++;
			}

		}

		foreach ($valid_emails as $entry) {
			$imploded_emails_string = implode(",", $entry);
			$query = "INSERT IGNORE INTO subscribers (email) VALUES ".$imploded_emails_string;

			//output from batch
			$result = mysql_query($query);
			$affected_results += mysql_affected_rows();

			//statistics
			$total_valid_emails += count($entry);

			//errors
			$errors = ($result == false) ? mysql_error() : false;
		}

		//se os e-emails tiverem sido inseridos
		if(!$errors){

			//se os subscritores já existirem nos registos, os resultados afectados serão 0. Terá que ser feito um join para inserir na tabela de relação
			if($affected_results == 0){
				$counter = 0;
				$split = 0;

				foreach ($list_valid_emails as $email_entry) {

					if($counter > 30000)
						$split++;

					$imploded_association_emails[$split][] = "((select id FROM subscribers where email = '".$email_entry."'), ".$group_id.")";
					$counter++; //serve apenas para segmentar o query para não exceder o "max_allowed_packet size"

				}

				foreach ($imploded_association_emails as $email_string) {
					$imploded_association_emails_string = implode(", ", $email_string);
					$sql = "REPLACE INTO subscriber_by_cat (id_subscriber, id_categoria) VALUES ".$imploded_association_emails_string;

					$result = mysql_query($sql);
				}
				
				$affected_results = mysql_affected_rows();

				if($affected_results)
					echo "<div class=\"alert alert-success\">Os endereços que já existiam no sistema foram associados com sucesso ao grupo seleccionado.</div>";

				else
					echo "<div class=\"alert alert-danger\">Erro: ".mysql_error()."</div>";
			}

			//caso contrário, os registos foram inseridos e basta associar os ultimos registos inseridos ao grupo
			else{
				//associar ao grupo utilizando os $affected_results
				$sql = "INSERT INTO subscriber_by_cat (id_subscriber, id_categoria) select id AS id_subscriber, {$group_id} as id_categoria from subscribers ORDER BY id DESC LIMIT {$affected_results}";
				$insert = mysql_query($sql);
				//feedback
				echo '<div class="alert alert-success">Inseridos <b>'.$affected_results.'</b> subscritores ao grupo seleccionado.</div>';
			}

			Tools::remember_subscriber_tab($group_id);

			if(count($invalid_emails > 0)){

				$total_invalid_emails = count($invalid_emails);
				$total_emails = $total_invalid_emails + $total_valid_emails;
				
				echo "<div class=\"alert alert-info\">Foram identificados <b>".$total_emails."</b> endere&ccedil;os no ficheiro seleccionado.</div>";

				//mostrar os e-mails que não foram inseridos para o utilizador os corrigir manualmente se possível
				if(!empty($list_invalid_emails)){
					$invalid_emails_html = implode("<br />", $list_invalid_emails); ?>

					<div class="alert alert-danger"><b><?php echo $total_invalid_emails ?></b> e-mails foram considerados inv&aacute;lidos</div>

					<div class="accordion" id="accordion2">
						<div class="accordion-group">
							<div class="accordion-heading">
							  <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
								Ver e-mails inv&aacute;lidos:
							  </a>
							</div>
							<div id="collapseOne" class="accordion-body collapse" style="height: 0px;">
							  <div class="accordion-inner">
								<?php echo $invalid_emails_html ?>
							  </div>
							</div>
				  		</div>
					</div>

				<?php
				}
				
			}
		}

		else{
			echo "<div class=\"alert alert-info\">N&atilde;o foram efectuadas altera&ccedil;&otilde;es aos subscritores: ".$errors."</div>";
		}
				
	}
	


	function import_csv(){

		$group_id = (int) $_POST["csv_group_id"];
		$file = $_FILES["csv"]["tmp_name"];

		if(empty($file)){
			echo "<div class=\"alert alert-danger\">N&atilde;o foi seleccionado um ficheiro para importa&ccedil;&atilde;o.</div>";
			return false;
		}
			


		if( $_POST['enclosed_by'] != '' ) {
			$enclosed_by = " optionally enclosed by '".$_POST['enclosed_by']."'";
			//$enclosed_by = '';
		}else{
			$enclosed_by = '';
		}
	

		//abrir o ficheiro file
		$handle = fopen($file, "r");

		$i = 0; //itera pelos e-mails
		$f = 0; //itera a cada 10000 ou outro valor, para separar a inserção em multiplos queries devido à limitação do servidor

		//fgetcsv remove logo os espaços "à la TRIM()"
		while (($data = fgetcsv($handle, 200, ",")) !== FALSE) {

			//se for um e-mail válido
			if(Tools::is_email($data[1])){
				$valid_emails[$f][] = "('".$data[0]."', '".$data[1]."' )";
				$list_valid_emails[] = $data[1];
			}				
			else{
				$invalid_emails[] = "('".$data[0]."', '".$data[1]."' )";
				$list_invalid_emails[] = $data[1];
			}

			$i++;

			//incrementa o $f pois já existem 10000 e-mails válidos no último batch
			if($i % 10000 == 0){
				$f++;
			}

		}

		//terminar a leitura
		fclose ($handle);

		foreach ($valid_emails as $entry) {
			$imploded_emails_string = implode(",", $entry);
			$query = "INSERT IGNORE INTO subscribers (nome, email) VALUES ".$imploded_emails_string;

			//output from batch
			$result = mysql_query($query);
			$affected_results += mysql_affected_rows();

			//statistics
			$total_valid_emails += count($entry);

			//errors
			$errors = ($result == false) ? mysql_error() : false;
		}

		//se os e-emails tiverem sido inseridos
		if(!$errors){

			//se os subscritores já existirem nos registos, os resultados afectados serão 0. Terá que ser feito um join para inserir na tabela de relação
			if($affected_results == 0){
				foreach ($list_valid_emails as $email_entry) {
					$imploded_association_emails[] = "((select id FROM subscribers where email = '".$email_entry."'), ".$group_id.")";
				}

				$imploded_association_emails_string = implode(", ", $imploded_association_emails);
				$sql = "INSERT INTO subscriber_by_cat (id_subscriber, id_categoria) VALUES ".$imploded_association_emails_string;
				$result = mysql_query($sql);
				$affected_results = mysql_affected_rows();

				if($affected_results)
					echo "<div class=\"alert alert-success\">Os endereços já existiam no sistema. Os ".$affected_results." endere&ccedil;os foram associados com sucesso ao grupo seleccionado.</div>";

				else
					echo "<div class=\"alert alert-danger\">Erro: ".mysql_error()."</div>";
			}

			//caso contrário, os registos foram inseridos e basta associar os ultimos registos inseridos ao grupo
			else{
				//associar ao grupo utilizando os $affected_results
				$sql = "INSERT INTO subscriber_by_cat (id_subscriber, id_categoria) select id AS id_subscriber, {$group_id} as id_categoria from subscribers ORDER BY id DESC LIMIT {$affected_results}";
				$insert = mysql_query($sql);
				//feedback
				echo '<div class="alert alert-success">Inseridos <b>'.$affected_results.'</b> subscritores ao grupo seleccionado.</div>';
			}

			if(count($invalid_emails > 0)){

				$total_invalid_emails = count($invalid_emails);
				$total_emails = $total_invalid_emails + $total_valid_emails;
				
				echo "<div class=\"alert alert-info\">De um total de <b>".$total_emails."</b> endere&ccedil;os encontrados no ficheiro, foram adicionados ".$affected_results." ao grupo selecionado.</div>";

				//mostrar os e-mails que não foram inseridos para o utilizador os corrigir manualmente se possível
				if(!empty($list_invalid_emails)){
					$invalid_emails_html = implode("<br />", $list_invalid_emails); ?>

					<div class="alert alert-danger"><b><?php echo $total_invalid_emails ?></b> e-mails foram considerados inv&aacute;lidos</div>

					<div class="accordion" id="accordion2">
						<div class="accordion-group">
							<div class="accordion-heading">
							  <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
								Ver e-mails inv&aacute;lidos:
							  </a>
							</div>
							<div id="collapseOne" class="accordion-body collapse" style="height: 0px;">
							  <div class="accordion-inner">
								<?php echo $invalid_emails_html ?>
							  </div>
							</div>
				  		</div>
					</div>

				<?php
				}
				
			}
		}

		else{
			echo "<div class=\"alert alert-info\">N&atilde;o foram efectuadas altera&ccedil;&otilde;es aos subscritores: ".$errors."</div>";
		}
				
	}
	
	
	function add_categoria(){
		$core = new Core();
		$tools = $core->getTools();

		$query = "INSERT INTO `newsletter_categorias` (`categoria`, `is_default`) VALUES ('".$tools->getPost('categoria_nome')."', '".$tools->getPost('defeito')."')";
		mysql_query( $query ) or die( mysql_error() );
			
		//Caso seja para adicionar todos os subscritores a esta newsletter
		if( $tools->getPost('add_subs') == 1 ) {
			
			$query = "SELECT * FROM `newsletter_categorias` ORDER BY `id` DESC LIMIT 1";
			$res = mysql_query( $query ) or die( mysql_error() );
			$id = mysql_fetch_object( $res );
			
			$query = "INSERT INTO `subscriber_by_cat` (SELECT NULL, `subscribers`.`id`, ".$id->id." FROM `subscribers` WHERE `subscribers`.`is_active`=1)";
			mysql_query( $query ) or die( mysql_error().$query );
			
			
		}
	}
	

}

?>