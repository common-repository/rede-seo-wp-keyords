<?php

/*

Plugin Name: Rede SEO WP-keyords

Plugin URI: http://www.redeseo.com.br

Description: Aumente a relevancia do seu blog com as palavras pesquisadas no google , yahoo, Bing.

Version: 1.0

Author: Rede SEO 

Author URI: http://www.redeseo.com.br

*/



include("seoqueries.inc");



if (!function_exists('pa')){

	function pa($mixed, $stop = false) {

	   $ar = debug_backtrace(); $key = pathinfo($ar[0]['file']); $key = $key['basename'].':'.$ar[0]['line'];

	   $print = array($key => $mixed); echo( '<pre>'.(print_r($print,1)).'</pre>' );

	   if($stop == 1) exit();

	}

}



register_activation_hook(__FILE__, 'seoqueries_install');

add_action('init', 'seoqueries_init');

add_action('init', 'register_seoqueries_widget');

add_action('admin_menu', 'seoqueries_admin_menu');

add_action('wp_head','seoqueries_wp_head');






function seoqueries_install(){

	global $wpdb;

	$sql = " CREATE TABLE IF NOT EXISTS `". $wpdb->prefix ."seoqueries_terms` (

			`stid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Search term id',

			`term_value` VARCHAR( 255 ) NOT NULL

			) ENGINE = MYISAM ";

	$wpdb->query($sql);

	$sql = " CREATE TABLE IF NOT EXISTS `". $wpdb->prefix ."seoqueries_data` (

			`stid` INT NOT NULL ,

			`founded` INT NOT NULL ,

			`page_type` VARCHAR( 100 ) NOT NULL ,

			`page_id` INT NOT NULL ,

			PRIMARY KEY ( `stid` , `page_id` )

			) ENGINE = MYISAM ";

	$wpdb->query($sql);

	$sql = "CREATE TABLE IF NOT EXISTS `". $wpdb->prefix ."seoqueries_terms_stats` (

			`id` SERIAL NOT NULL ,

			`stid` BIGINT NOT NULL ,

			`page_type` VARCHAR( 100 ) NOT NULL ,

			`page_id` INT NOT NULL ,

			`position` INT NULL ,

			`date_clicked` INT NOT NULL

			) ENGINE = MYISAM ;

				";

	$wpdb->query($sql);

}



function seoqueries_wp_head(){

	$theme_dir = get_bloginfo('stylesheet_directory');

	$theme_dir = str_replace(get_bloginfo('url').'/','',$theme_dir);



	if (file_exists( $theme_dir.'/seoqueries.css')){

		echo '<link href="'. get_bloginfo('stylesheet_directory').'/seoqueries.css' .'" rel="stylesheet" type="text/css" />';

	}else{

		echo '<link href="'. get_bloginfo('url').'/wp-content/plugins/rede-seo-wp-keyords/seoqueries.css' .'" rel="stylesheet" type="text/css" />';

	}

}



function seoqueries_admin_menu(){

	add_options_page('Rede SEO WP-keyords', 'Rede SEO WP-keyords', 8, basename(__FILE__), 'seoqueries_options_form');

}





function seoqueries_options_form(){

	$base_url = $_SERVER['REQUEST_URI'];

	$rpos = strrpos($base_url,'&cat=');

	if ($rpos){

		$base_url = substr($base_url,0,$rpos);

	}

	$cat = $_GET['cat'];

	?>

	<a href="<?php echo $base_url.'&amp;cat=general' ?>">Configura&ccedil;&otilde;es gerais</a>
| 
	<a href="<?php echo $base_url.'&amp;cat=search_terms' ?>"><span id="result_box10"><span title="">Termos de pesquisa</span></span></a>

	| <a href="<?php echo $base_url.'&amp;cat=keyword-search' ?>"><span id="result_box11"><span title="">Busca por palavra-chave</span></span></a>

    | <a href="http://www.redeseo.com.br" target="_blank"><strong>REDE SEO</strong></a>

	<?php if ($cat == 'general' || empty($cat)): ?>

    <div class="wrap">

    <h2>Rede SEO WP-keyword</h2>

    <form method="post" action="options.php">

	    <?php wp_nonce_field('update-options'); ?>

	    <h3>Configura&ccedil;&otilde;es Gerais:</h3>

	    <h3><span id="result_box"><span title="">Digite o n&uacute;mero m&aacute;ximo de palavras-chave que voc&ecirc; deseja que apare&ccedil;a em suas p&aacute;ginas:</span></span></h3>

<input type="text" name="seoqueries_tags_limit" value="<?php echo get_option('seoqueries_tags_limit',10) ?>" /><br /><br />

	    <h3><span id="result_box2"><span title="">Defina o seu estilo (em ordem crescente, por exemplo: H4, H3, H2)</span></span></h3>

      <input type="text" size="80" name="seoqueries_tags" value="<?php echo get_option('seoqueries_tags','strong,h6,h5,h4,h3,h2') ?>" /><br /><br />

	    <h3><span id="result_box3"><span title="">Digite o texto que aparece quando uma p&aacute;gina n&atilde;o temresultados de pesquisa org&acirc;nica  ... </span><span title="">(Texto predefinido)</span></span></h3>

      <input type="text" size="80" name="seoqueries_no_terms_messge" value="<?php echo get_option('seoqueries_no_terms_messge','Ninguem chegou nesta pagina vindo de um mecanismo de busca, ainda!!') ?>" /><br /><br />

	    <input type="hidden" name="action" value="update" />

	    <input type="hidden" name="page_options" value="seoqueries_tags_limit,seoqueries_tags,seoqueries_no_terms_messge" />

	    <input type="submit" name="update" value="Slavar">

    </form>

    <h3>Coloque-o no seu site:</h3>

    <p>P<span id="result_box4"><span title="">ara   fezer este plugin funcionar no seu site, voc&ecirc; precisar&aacute; adicionar o   <strong>Rede SEO WP-keyword</strong> na tela de configura&ccedil;&atilde;o do widget.</span></span></p>

    <p>&nbsp;</p>
<h3>Qualquer d&uacute;vida entre em contato no site, <a href="http://www.redeseo.com.br" target="_blank">www.redeseo.com.br</a></h3>

</div>

	<? elseif ($cat == 'keyword-search'): ?>

		<div>

			<h2><span id="result_box5"><span title="">Buscar  palavra-chave</span></span></h2>

			<form id="seoqueries-keywords-search" action="" method="post">

				<div><span id="result_box6"><span title="">Digite palavra-chave de busca</span></span> 
				  <input id="keyword" type="text" name="keyword" value="<?php echo $_POST['keyword'] ?>"  /><br />

					<input type="checkbox" id="exact-day" value="1" />
					<span id="result_box7"><span title="">Mostrar palavras-chave na data exata</span></span>
					<input type="text" id="exact-date" name="exact_date" value="<?php echo $_POST['exact_date'] ?>"  /><br />

					<label style="float:left;width:225px;">Digite a data de inicio (YYYY-mm-dd)</label>

					<input type="text" id="start-date" name="start_date" value="<?php echo $_POST['start_date'] ?>"  /><br />

					<label style="float:left;width:225px;">Digite a data final (YYYY-mm-dd)</label>

					<input type="text" id="end-date" name="end_date" value="<?php echo $_POST['end_date'] ?>"  /><br />

					<input type="submit" value="Buscar" />

			  </div>

		  </form>

			<div id="seoqueries-search-result">

			</div>

		  <script type="text/javascript">

				var baseUrl = "<?php bloginfo('url') ?>/";

			</script>

		  <script type="text/javascript">

				<?php

					echo file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR.'keywords.js');

				?>

			</script>

</div>

    <?php else: ?>

    <div class="wrap">

    	<h2><span id="result_box8"><span title="">Os termos de pesquisa</span></span></h2>

    	<?php

    		global $wpdb;

    		$sql = "SELECT st.stid,st.term_value,std.page_id,std.page_type,std.founded FROM ".$wpdb->searchterms ." st".

    				" INNER JOIN ".$wpdb->searchterms_data ." std ON std.stid = st.stid ".

    				"WHERE page_type='home' AND page_id=0 

    				ORDER BY std.founded DESC ";

    		$rows = $wpdb->get_results($sql);

    		if (!empty($rows)){

    			_e('<h3>Consultas de pesquisa (Na Home)</h3>');

    			echo '<table width="500" cellpadding="0" cellspacing="0">';

    			echo '<thead>';

    			echo '<tr><td><strong>Palavras buscadas</strong></td><td><strong>Numero de buscas</strong></td><td><strong>Posicao no google</strong></td><td><strong>Ultimo cliqued</strong></td></tr>';

    			echo '</thead><tbody>';

    			foreach ($rows as $row){

    				echo '<tr>';

    				echo '<td>'. $row->term_value .'</td><td>'. $row->founded .'</td>';

    				$position = seoqueries_get_google_position($row->stid);

    				$clicked_date = seoqueries_last_clicked_date($row->stid,$row->page_type,$row->page_id);

    				echo '<td>'. ( empty($position) ? "-" : $position ) .'</td>';

    				echo '<td>'. ( empty($clicked_date) ? "-" : date("Y-m-d H:i",$clicked_date) ) .'</td>';

    				echo '</tr>';

    			}

    			echo '</tbody></table>';

    		}



    		$sql = "SELECT st.stid,st.term_value,std.page_id,std.page_type,std.founded FROM ".$wpdb->searchterms ." st".

    				" INNER JOIN ".$wpdb->searchterms_data ." std ON std.stid = st.stid ".

    				"WHERE page_type<>'home' AND page_id<>0

    				 ORDER BY std.founded DESC";

    		$rows = $wpdb->get_results($sql);

    		if (!empty($rows)){

    			$formatted_rows = array();

    			foreach ($rows as $row){

    				$formatted_rows[$row->page_type][$row->page_id][] = $row; 

    			}

    			echo '<table width="500" cellpadding="0" cellspacing="0" style="margin-top:30px">';

    			echo '<thead>';

    			echo '<tr><td><strong>Palavra buscada</strong></td><td><strong>Numero de buscas</strong></td><td><strong>Posicao no google</strong></td><td><strong>Ultimo clique</strong></td></tr>';

    			echo '</thead><tbody>';

    			foreach($formatted_rows as $section => $section_terms ){

    				echo '<tr><td colspan="2"><h3>'. ucfirst($section) .' search terms</h3><hr /></td></tr>';

    				foreach ($section_terms as $section_id => $terms){

    					echo '<tr><td colspan="2"><h4>'. seoqueries_get_item_title($section,$section_id) .'</h4><hr /></td></tr>';

		    			foreach ($terms as $row){

		    				echo '<tr>';

		    				echo '<td>'. $row->term_value .'</td><td>'. $row->founded .'</td>';

		    				$position = seoqueries_get_google_position($row->stid);

		    				$clicked_date = seoqueries_last_clicked_date($row->stid,$row->page_type,$row->page_id);

		    				echo '<td>'. ( empty($position) ? "-" : $position ) .'</td>';

		    				echo '<td>'. ( empty($clicked_date) ? "-" : date("Y-m-d H:i",$clicked_date) ) .'</td>';

		    				echo '</tr>';

		    			}

    				}

    			}

    			echo '</tbody></table>';

    		} 

    	?>

</div>

    <?php endif; ?>

    <?

}




function seoqueries_init(){

	global $wp,$wp_query;

	

	

	wp_enqueue_script('jquery',"wp-includes/js/jquery/jquery.js");

	

	$wp->parse_request();

	$wp_query->parse_query($wp->query_vars);

	

	global $wpdb;

	$wpdb->searchterms = $wpdb->prefix .'seoqueries_terms';

	$wpdb->searchterms_data = $wpdb->prefix .'seoqueries_data';

	$wpdb->searchterms_stats = $wpdb->prefix .'seoqueries_terms_stats';

	

	if (!empty($_POST['seoqueries_keyword_search'])){

		seoqueries_keywords_search_process();

		exit();

	}

	

	//imitating http_referer variable uncomment this line if you want to test plugin

	//$_SERVER['HTTP_REFERER'] = 'http://google.com/?q=jls tickets please!&cd=2  ';

	

	//pa($GLOBALS,1);

	

	$ref = seoqueries_get_refer();

	if (seoqueries_getinfo('isref')){

		$referer = seoqueries_get_refer();

	    $delimiter = seoqueries_get_delim($referer);

	    $terms = seoqueries_get_terms($delimiter);

	    

	    //pa($terms,1);

	    

	    $sql = "SELECT * FROM ". $wpdb->searchterms ." WHERE term_value='". $terms ."'";

	    $term = $wpdb->get_row($sql);

	    if (empty($term)){

	    	$wpdb->insert($wpdb->searchterms,array('term_value' => $terms),array('%s'));

	    	$sql = "SELECT * FROM ". $wpdb->searchterms ." WHERE term_value='". $terms ."'";

	    	$term = $wpdb->get_row($sql);

	    }

		if (empty($term)){

			return;

		}

		$type_id = seoqueries_get_type_id();

		global $seoqueries;

		$seoqueries = new stdClass();

		$seoqueries->type = $type_id['type'];

		$seoqueries->id = $type_id['id'];

	    

		$sql = "SELECT *  FROM ". $wpdb->searchterms_data ." WHERE stid=". $term->stid ." AND page_type='". $type_id['type'] ."' AND page_id=". $type_id['id'];

		$row = $wpdb->get_row($sql);

		if (empty($row)){

			$data = array(

						'stid' => $term->stid,

						'page_type' => $type_id['type'],

						'page_id' => $type_id['id'],

						'founded' => 1

					);

			$wpdb->insert($wpdb->searchterms_data,$data,array('%d','%s','%d','%d'));

		}else{

			$row->founded++;

			$wpdb->update($wpdb->searchterms_data,

							array('founded' => $row->founded),

							array('stid' => $term->stid,'page_type' => $type_id['type'],'page_id'=>$type_id['id']),

							array('%d'),

							array('%d','%s','%d')

							);

		}

		

		$data = array(

			'stid' => $term->stid,

			'page_type' => $type_id['type'],

			'page_id' => $type_id['id'],

			'date_clicked' => time()

		);

		$ref_url = $_SERVER['HTTP_REFERER'];

		$ref_url_attr = parse_url($ref_url);

		parse_str($ref_url_attr['query'],$ref_url_query);

		

		if(!empty($ref_url_query['cd'])){

			$data['position'] = $ref_url_query['cd']; 

		}

		//pa($data);

		//pa($ref_url_query,1);

		$wpdb->insert($wpdb->searchterms_stats,$data,array('%d','%s','%d','%d','%d'));

	}

}





function seoqueries_widget($args) {

    extract($args);

    echo $before_widget;

    echo $before_title;

    echo __(get_option('seoqueries_widget_title','Fuzzy SEO Queries'));

    echo $after_title;

    

    $plaint_text = get_option('seoqueries_widget_plain_text',0);

    seoqueries_get_page_terms($plaint_text);

    

    echo $after_widget;

}





function seoqueries_widget_control(){

	if (!empty($_REQUEST['seoqueries_widget_title'])){

		update_option('seoqueries_widget_title', $_REQUEST['seoqueries_widget_title']);

		if (!empty($_REQUEST['seoqueries_widget_plain_text'])){

			update_option('seoqueries_widget_plain_text', 1);

		}else{

			update_option('seoqueries_widget_plain_text', 0);

		}

	}

	

	_e('Widget title');

	echo '<input type="text" name="seoqueries_widget_title" value="'. get_option('seoqueries_widget_title','Seoqueries terms') .'" />';

	echo '<br />';

	$plain_text = get_option('seoqueries_widget_plain_text',0);

	echo $plaint_text;

	$checked = $plain_text ? 'checked="checked"' : '';

	echo '<input type="checkbox" name="seoqueries_widget_plain_text" '. $checked .' />';

	_e('Display as plain text(otherwise will be displayed as unordered list)');

}




function register_seoqueries_widget() {

    register_sidebar_widget('Seoqueries terms', 'seoqueries_widget');

    register_widget_control('Seoqueries terms', 'seoqueries_widget_control' );

}





function seoqueries_get_page_terms($plain_text = false){

	global $seoqueries,$wpdb;

	

	if (empty($seoqueries)){

		$type_id = seoqueries_get_type_id();

		$seoqueries = new stdClass();

		$seoqueries->type = $type_id['type'];

		$seoqueries->id = $type_id['id'];	

	}

	

	if (!empty($seoqueries)){

		$sql = "SELECT st.term_value, std.founded FROM ".$wpdb->searchterms . " st ".

			   " INNER JOIN ".$wpdb->searchterms_data ." std ON st.stid = std.stid ".

			   " WHERE std.page_type='". $seoqueries->type ."' AND std.page_id=". $seoqueries->id .

			   " ORDER BY std.founded DESC LIMIT ". get_option('seoqueries_tags_limit',20);

		$terms = $wpdb->get_results($sql);

		

		if (empty($terms)){

			$no_terms_message = get_option('seoqueries_no_terms_messge','Ninguém chegou nesta página vindo de um mecanismo de busca, ainda!!');

			echo $no_terms_message;

			return;

		}

		

		$max_founded = current($terms)->founded;

		$min_founded = end($terms)->founded;

		

		shuffle($terms);

		

		

		if (!empty($terms)){

			if (!$plain_text){

				echo '<ul class="seoqueries-terms">';

			}

			foreach($terms as $term ){

				if (!$plain_text){

					$tag = seoqueries_get_tag($term,$min_founded,$max_founded);

					echo '<li><'. $tag .'>'. $term-> term_value .' </'.$tag.'></li> ';

				}else{

					echo  $term->term_value .' , ';

				}

			}

			

			if (!$plain_text){

				echo '<li><a href="http://www.redeseo.com.br/seo/novo-plugin-wordpress-para-seo-rede-seo-wp-keyords/">Rede seo</a></li>';

				echo '</ul>

				';

			}else{

			echo '<a href="http://www.redeseo.com.br/seo/novo-plugin-wordpress-para-seo-rede-seo-wp-keyords/">Rede seo</a>';

			}

		}

	}

}



function seoqueries_keywords_search_process(){

	global $wpdb;

	require_once(ABSPATH."/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php");

	//$json_obj = new Moxiecode_JSON();

	//$json = $json_obj->encode(array("key1"=>"value1","key2"=>"value2"));

	//$json should have {"key1":"value1","key2":"value2"}

	$keyword = $_POST['keyword'];

	$start_date = $_POST['startDate'];

	$end_date = $_POST['endDate'];

	

	$sql = "SELECT * FROM {$wpdb->searchterms} WHERE term_value ='{$wpdb->escape($keyword)}'";

	$row = $wpdb->get_row($sql);

	if (!empty($row)){

		$results = array(

			'result' => 'term_info',

			'row' => $row

		);

		$sql = "SELECT * FROM {$wpdb->searchterms_stats} WHERE stid={$row->stid}";

		if ($_POST['exactDay']==1){

			$exact_date = $_POST['exactDate'];

			$exact_timestamp = @strtotime($exact_date);

			if ($exact_timestamp>0){

				$day = date("d",$exact_timestamp);

				$month = date("m",$exact_timestamp);

				$year = date("Y",$exact_timestamp);

				

				$sql .=" AND date_clicked > ".mktime(0,0,0,$month,$day,$year);

				$sql .=" AND date_clicked < ".mktime(0,0,0,$month,$day+1,$year);

			}

		}else{

			if (!empty($start_date)){

				$start_timestamp = @strtotime($start_date);

				if ($start_timestamp>0){

					$sql .= " AND date_clicked > $start_timestamp ";

				}

			}

			if (!empty($end_date)){

				$end_timestamp = @strtotime($end_date);

				if ($end_timestamp>0){

					$sql .= " AND date_clicked < $end_timestamp ";

				}

			}

		}

		$sql .=" ORDER BY date_clicked DESC";

		



		$items = $wpdb->get_results($sql);

		$results['items'] = $items;

		$itemsHtml = "
<p>Keyword: ". $row->term_value ."</p>";

		$itemsHtml .= "<table>";

		$itemsHtml .="<thead>";

		$itemsHtml .="<tr>";

		$itemsHtml .='<td width="200"><strong>Data</strong></td>';


		$itemsHtml .="</tr>";

		$itemsHtml .="</thead>";

		$itemsHtml .="<tbody>";

		

		foreach ($items as $item){

			$itemsHtml .="<tr>";

			$itemsHtml .="<td>". date("d/m/Y H:i",$item->date_clicked) ."</td>";

			$position = " - ";

			if (!empty($item->position)){

				$position = $item->position;

			}

			$itemsHtml .="<td>". $position ."</td>";

			$itemsHtml .="</tr>";

		}

		

		$itemsHtml .="</tbody>";

		$itemsHtml .="</table>";

		

		$results['itemsHtml'] = $itemsHtml;

	}else{

		//matching posible search terms

		$sql = "SELECT * FROM {$wpdb->searchterms} WHERE term_value LIKE '%{$wpdb->escape($keyword)}%'";

		$items = $wpdb->get_results($sql);

		if (!empty($items)){

			$results = array(

				'result' => 'terms_listing',

				'items' => $items

			);

		}else{

			$results = array(

				'result' => 'terms_listing',

				'not_found' => 1

			);

		}

	}

	require_once(ABSPATH."/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php");

	$json_obj = new Moxiecode_JSON();

	$json = $json_obj->encode($results); 

	echo $json;	

}