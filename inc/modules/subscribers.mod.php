<?php 
/**
* 
*/
class Subscribers
{
	public $view = "subscribers/subscribers";
	
	function __construct()
	{
		if (!empty($_GET["view"])) {
			$this->$_GET["view"]();
		}

	}

	/* Controllers */
	function import_file(){
		//$this->view = "empty";
		
		ini_set("memory_limit", "-1");

		$group_id = (int) $_POST["csv_group_id"];
		$file = $_FILES["csv"]["tmp_name"];

		if(empty($file)){
			tools::notify_add( "N&atilde;o foi seleccionado um ficheiro para importa&ccedil;&atilde;o.", "error");
			redirect( "index.php?mod=subscribers" );
			return false;
		}
		
		$string = file_get_contents($file); // Load text file contents
		$string = str_replace("\\", "", $string);

		$matches = array(); //create array
		//$pattern = '/[.A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_\.]+)/'; //regex for pattern of e-mail address
		$pattern = '/[^0-9][A-z0-9_-]+([.][A-z0-9_-]+)*[@][A-z0-9-]+([.][A-z0-9-]+)*[.][A-z]{2,4}/';
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

				if($affected_results) {
					tools::notify_add( "Os endereços que já existiam no sistema foram associados com sucesso ao grupo seleccionado.", "error");
				} else {
					tools::notify_add( "Erro: ".mysql_error(), "error");
				}
			}

			//caso contrário, os registos foram inseridos e basta associar os ultimos registos inseridos ao grupo
			else{
				//associar ao grupo utilizando os $affected_results
				$sql = "INSERT INTO subscriber_by_cat (id_subscriber, id_categoria) select id AS id_subscriber, {$group_id} as id_categoria from subscribers ORDER BY id DESC LIMIT {$affected_results}";
				$insert = mysql_query($sql);
				//feedback
				tools::notify_add( 'Inseridos <b>'.$affected_results.'</b> subscritores ao grupo seleccionado.', "success" );
				redirect( "admin/index.php?mod=subscribers" );
			}

			Tools::remember_subscriber_tab($group_id);

			$this->invalid_emails = $invalid_emails;
		}

		else{
			echo "<div class=\"alert alert-info\">N&atilde;o foram efectuadas altera&ccedil;&otilde;es aos subscritores: ".$errors."</div>";
		}
				
	}


	function multiple_action() {
		if( $_POST['action'] == 'delete' || $_POST['action'] == 'activate' || $_POST['action'] == 'deactivate' ){
			if ($_POST["action"] == "delete" ) {
				$query = "delete from subscriber_by_cat where id_subscriber IN (".implode($_POST["items"], ",") . ")";
				mysql_query($query) or die( mysql_error() );
				tools::notify_add( "Subcritores eliminadoss com sucesso", "success" );
			}
			tools::doAction('subscribers',$_POST['action'],$_POST['items'],0,0);
		}else{
			$action = $_POST['action'];
			$action_a = explode("_", $action);
			$items = $_POST['items'];
			if( $action_a[0] == 'add' ) {
				$query = "DELETE FROM `subscriber_by_cat` WHERE `id_categoria` = '".$action_a[1]."'";
				mysql_query( $query ) or die( mysql_error() );
				$i = 0;
				while( isset($items[$i]) ) {
					$query = "INSERT INTO `subscriber_by_cat` (`id_subscriber`, `id_categoria`) VALUES ('".$items[$i]."', '".$action_a[1]."')";
					mysql_query($query) or die(mysql_error());
					$i++;
				}
				tools::notify_add( "Categorias alteradas", "success" );
			}elseif($action_a[0] == 'remove'){
				$query = "DELETE FROM `subscriber_by_cat` WHERE `id_categoria` = '".$action_a[1]."'";
				mysql_query( $query ) or die( mysql_error() );
				tools::notify_add( "Subscritores eliminados.", "success" );
			}
		}
	}

	function add_to_exclusion() {

		$emails_list = $_POST["add_to_exclusion"];
		$emails = $this->find_emails_in_string($emails_list);
		
		if($emails) {
			$this->db_add_to_exclusion($emails);
		} else {
			tools::notify_add( "N&atilde;o foram encontrados e-mails v&aacute;lidos para adicionar &agrave; lista de exclus&atilde;o", "error" );
		}
		redirect( "admin/index.php?mod=subscribers" );
	}


	function add_subscriber() {
		
		//$core = new Core();
		//$tools = $core->getTools();

		//dados a inserir
		$email = tools::getPost('email');
		$nome = tools::getPost('nome');
		$is_active = tools::getPost('is_active');
		(int) $group_id = tools::getPost("group_id");

		$sql = "INSERT into subscribers set nome = '{$nome}', email = '{$email}', is_active = {$is_active}";
		$res = mysql_query($sql);

		if ($res == true) {
			tools::notify_add("Endereço de email adicionado com sucesso &agrave; lista de subscritores", "success" );
		}else {
			tools::notify_add("O endere&ccedil;o de email j&aacute; se encontra na lista de subscritores", "error" );
		}
		

		//fixed: não se podia ir buscar o subscriber por id, pois nem sempre é inserido. por vezes já existe e só é necessário fazer a atribuição				
		$query = "SELECT * FROM `subscribers` WHERE email = '{$email}' LIMIT 1";
		$res = mysql_query( $query ) or die(mysql_error());
		
		$id = mysql_fetch_object( $res );

		$query = "SELECT * FROM `newsletter_categorias` WHERE `is_default` = '1'";
		$res = mysql_query( $query ) or die(mysql_error());

		//adicionar ao grupo
		$sql = "INSERT INTO `subscriber_by_cat` (`id_subscriber`, `id_categoria`) VALUES ( {$id->id}, {$group_id} )";
		$res = mysql_query($sql);

		if($res == true){
			tools::notify_add( "O endere&ccedil;o de email foi associado ao grupo seleccionado", "success" );
		}else{
			tools::notify_add( "O endere&ccedil;o de email j&aacute; est&aacute; associado ao grupo seleccionado", "error" );
		}
		
		while( $row = @mysql_fetch_object($res) ) {
			$query ="INSERT INTO `subscriber_by_cat` (`id_subscriber`, `id_categoria`) VALUES ('".$id->id."', '".$row->id."')";
			mysql_query( $query ) or die( mysql_error() );
		}
		
		Tools::remember_subscriber_tab($group_id);

		//redirect to listing
		redirect ( "admin/index.php?mod=subscribers" );

		//@header("Location: "._ROOT."?mod=newsletter", TRUE, 302);
	}





	/* MODELS */
	function find_emails_in_string($string){
		$string = str_replace("\\", "", $string);

		$matches = array(); //create array
		$pattern = '/[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}/';
		preg_match_all($pattern, $string, $matches); //find matching pattern

		$emails = $matches[0];
		$emails = array_map("trim", $emails);
		return empty($emails) ? false : $emails;

	}


	function db_add_to_exclusion($emails){
		if (empty($emails)){
			tools::notify_add( "N&atilde;o foram enviados e-mails para adicionar &agrave; base de dados", "error" );
			return false;
		}
		else{
			
			foreach ($emails as $email) {
				$query = "select * from subscribers where email = '".$email."'";
				$res = mysql_query($query) or die_sql( $query );
				if ( mysql_num_rows($res) == 0 ) {
					$query = "INSERT INTO `subscribers` (email, is_active) VALUES (\"$email\", 0)";
				}else{
					$query = "update subscribers set is_active = 0 where email = '".$email."'";
				}
				$insert = mysql_query($query);
			}

			tools::notify_add( "Foram adicionados ".count($emails)." endere&ccedil;os &agrave; lista de exclus&atilde;o", "success" );
			return true;
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

	//listar os grupos a quais os utilizadores têm permissão
	public function get_admin_group_permissions(){

		//um utilizador is_admin tem acesso a tudo
		$sql = "SELECT nc.id AS categoria_id, nc.categoria AS categoria_nome from newsletter_categorias nc";	
		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$groups[$row->categoria_id] = $row->categoria_nome;

			return $groups;
		}

		return false;

	}

	//listar os grupos a quais os utilizadores têm permissão
	public function get_group_permissions($user_id){	//moved to subscribers mod

		//um utilizador is_admin tem acesso a tudo
		$sql = "SELECT nc.id AS categoria_id, nc.categoria AS categoria_nome from user_permissions up left join users u ON u.id = up.user_id left join newsletter_categorias nc ON up.group_id = nc.id where up.user_id = ".$user_id;	
		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$groups[$row->categoria_id] = $row->categoria_nome;

			return $groups;
		}

		return false;

	}




}

 ?>