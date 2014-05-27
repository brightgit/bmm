<?php 
/**
 * Dashboard
 */
class Grupos {

	public $view = "grupos/grupos";


	function __construct() {

		switch ( $_GET["view"] ) {
			case 'remover':
				$this->remove_grupo( $_GET["id"] );
				break;
			
			case 'add_grupo':
				$this->add_categoria( $_GET["id"] );
				break;
			
			case 'update_group':
				$this->update_grupo( $_GET["id"] );
				break;
			
			case 'edit':
				$this->view = "grupos/edit";
				break;
			
			default:	//Mostrar todos os grupos
				break;
		}


	}

	public function update_grupo($id){
		$nome = $_POST["group_name"];
		$is_default = $_POST["is_default"];

		$sql = "UPDATE `newsletter_categorias` SET `categoria` = '{$nome}', `is_default` = {$is_default} WHERE id = {$id}";
		$query = mysql_query($sql);




		tools::notify_add( "Grupo actualizado com sucesso.", "success" );

		return true;
	}


	public function remove_grupo($id){
		$sql = "DELETE FROM newsletter_categorias WHERE id = {$id}";
		$query = mysql_query($sql);

		$sql2 = "DELETE from subscriber_by_cat where id_categoria = ".$id;
		mysql_query($sql2);

		//Delete subscribers not in group
		$sql3 = "DELETE subscribers from subscribers left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id where subscriber_by_cat.id_categoria is NULL";
		mysql_query($sql3) or die_sql( $sql3 );

		tools::notify_add( "Grupo removido com sucesso.", "success" );
		redirect( "admin/index.php?mod=grupos&view=grupos" );
	}


	function add_categoria(){	//Added to groups

		$query = "INSERT INTO `newsletter_categorias` (`categoria`, `is_default`) VALUES ('".tools::getPost('categoria_nome')."', '".tools::getPost('defeito')."')";
		mysql_query( $query ) or die( mysql_error() );
			

		$query = "SELECT * FROM `newsletter_categorias` ORDER BY `id` DESC LIMIT 1";
		$res = mysql_query( $query ) or die( mysql_error() );
		$id = mysql_fetch_object( $res );	//Id do grupo

		$query = "insert into user_permissions set user_id = '".$_SESSION["user"]->id."', group_id = '".$id->id."'";
		mysql_query($query) or die_sql( $query );


		//Caso seja para adicionar todos os subscritores a esta newsletter
		if( tools::getPost('add_subs') == 1 ) {
			
			
			$query = "INSERT INTO `subscriber_by_cat` (SELECT NULL, `subscribers`.`id`, ".$id->id." FROM `subscribers` WHERE `subscribers`.`is_active`=1)";
			mysql_query( $query ) or die( mysql_error().$query );
			
			
		}
		tools::notify_add( "Grupo adicionado com sucesso", "success" );
		redirect( "admin/index.php?mod=grupos&view=grupos" );
	}

	public function get_grupo($id){

		$sql = "SELECT * from newsletter_categorias WHERE id = ".($id);
		$query = mysql_query($sql);

		if($query)
			$group = mysql_fetch_object($query);

		return $group;
	}

	function get_users_with_permission_in_group($group_id){	
		$sql = "SELECT u.id, u.first_name, u.last_name, u.username from user_permissions up inner join users u ON u.id = up.user_id WHERE group_id = {$group_id}";
		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$users[] = $row;

			return $users;
			
		}

		return false;

	}



}


?>