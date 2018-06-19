<?php
require_once __DIR__.'/../models/beerHop.php';
require_once __DIR__.'/../conn.php';

class BeerHopManager{

	function Save($beerHop){
		$sql = "";
		if($beerHop->get_id()){
			$sql = 	"UPDATE beers_hops " .
					"SET " .
						"name = '" . encode($beerHop->get_name()) . "', " .
						"beer_id = '" . encode($beerHop->get_beerID) . "', " .
						"name = '" . encode($beerHop->get_name()) . "', " .
						"alpha = '" . $beerHop->get_alpha() . "', " .
						"amount = '" . $beerHop->get_amount() . "', " .
						"time = '" . $beerHop->get_time() . "', " .
						"modifiedDate = NOW() ".
					"WHERE id = " . $beerHop->get_id();
					
		}else{		
			$sql = 	"INSERT INTO beers_hops(beer_id, name, alpha, amount, time, createdDate, modifiedDate ) " .
					"VALUES(" . 
					"'" . encode($beerHop->get_beerID()) . "', " .
					$beerHop->get_name() . ", " .
					"'" . encode($beerHop->get_alpha()) . "', " .
					"'" . $beerHop->get_amount() . "', " . 
					"'" . $beerHop->get_time() . "', " . 
					", NOW(), NOW())";
		}
		

		global $mysqli;
		$result=$mysqli->query($sql);
	}
	
	function GetById($id){
		$sql="SELECT * FROM beers_hops WHERE beer_id = $id order by time desc";
		global $mysqli;
		$qry=$mysqli->query($sql);
		
		 $beerHops = array();
		 while($i = $qry->fetch_array()){
			 $beerHop = new beerHop();
			 $beerHop->setFromArray($i);
			 $beerHops[$beerHop->get_id()] = $beerHop;	
		 }
		
		 return $beerHops;
	}
	
	// function Inactivate($id){
		// $sql = "SELECT * FROM taps WHERE beerId = $id AND active = 1";
		// global $mysqli;
		// $qry=$mysqli->query($sql);
		
		// if( $qry->fetch_array() ){		
			// $_SESSION['errorMessage'] = "Beer is associated with an active tap and could not be deleted.";
			// return;
		// }
	
		// $sql="UPDATE beers SET active = 0 WHERE id = $id";
		//echo $sql; exit();
		// $qry=$mysqli->query($sql);
		
		// $_SESSION['successMessage'] = "Beer successfully deleted.";
	// }
}