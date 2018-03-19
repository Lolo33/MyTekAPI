<?php

session_start();

global $db;
global $param;

function connexionBdd(){
    $hote = "localhost";
    $db = "mate_maker_api";
    $user = "root";
    $pass = "";
    try {
        $bdd = new PDO('mysql:host='.$hote.';dbname='.$db.';charset=utf8', $user, $pass);
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $bdd;
    } catch (Exception $e) {
        die('<b>Erreur de connexion à la Bdd :</b> <br>' . $e->getMessage());
    }
}
function sanitize($input) {
    return htmlspecialchars(trim($input));
}
function sanitize_tab($input) {
    return array_map('sanitize', $input);
}

function liste_terrain_lieu($complexe_id){
	$db = connexionBdd();
	$req = $db->prepare('SELECT * FROM  terrain INNER JOIN complexe ON terrain.complexe_id = complexe.id WHERE complexe.id = :complexe_id');
	$req->bindValue(":complexe_id", $complexe_id, PDO::PARAM_INT);
	$req->execute();
	return $req->fetchAll();
}
function liste_plages_horaires_terrain($terrain_id){
	$db = connexionBdd();
	$req = $db->prepare('SELECT * FROM  terrain INNER JOIN plage_horaire ON terrain.id = plage_horaire.terrain_id WHERE terrain.id = :terrain_id');
	$req->bindValue(":terrain_id", $terrain_id, PDO::PARAM_INT);
	$req->execute();
	return $req->fetchAll();
}
function liste_tarifs_terrain($terrain_id){
	$db = connexionBdd();
	$req = $db->prepare('SELECT * FROM  terrain INNER JOIN planning_tarif ON terrain.id = planning_tarif.terrain_id WHERE terrain.id = :terrain_id');
	$req->bindValue(":terrain_id", $terrain_id, PDO::PARAM_INT);
	$req->execute();
	return $req->fetchAll();
}
function recupResaById($resa_id){
	$db = connexionBdd();
	$req = $db->prepare('SELECT * FROM reservation WHERE id = :resa_id');
	$req->bindValue("resa_id", $resa_id, PDO::PARAM_INT);
	$req->execute();
	return $req->fetchAll();
}
function liste_aa(){
	$db = connexionBdd();
	$req = $db->prepare('SELECT * FROM utilisateur_api');
	$req->execute();
	return $req->fetchAll();
}
function liste_commissions_terrain_aa($terrain_id, $aa_id){
	$db = connexionBdd();
	$req = $db->prepare('SELECT * FROM planning_commission WHERE terrain_id = :terrain_id AND utilisateur_api_id = :aa_id');
	$req->bindValue(":terrain_id", $terrain_id, PDO::PARAM_INT);
	$req->bindValue(":aa_id", $aa_id, PDO::PARAM_INT);
	$req->execute();
	return $req->fetchAll();
}
function maj_commissions($heure_debut, $heure_fin, $terrains, $aa, $jours, $commission, $interdiction){
	// $heure_debut et $ heure_fin sont au format H:i:s
	// $terrains et $aa sont des tableaux
	// return false / true
	$db = connexionBdd();
	$obj_heure_plage_debut = DateTime::createFromFormat('H:i:s', $heure_debut);
	$obj_heure_plage_fin = DateTime::createFromFormat('H:i:s', $heure_fin);
	if ($obj_heure_plage_debut < $obj_heure_plage_fin) {
		foreach ($terrains as $terrain_key => $terrain_value) {
			foreach ($jours as $jour_key => $jour_value) {
				foreach ($aa as $aa_key => $aa_value) {

					$plages_com_terrain_aa = liste_commissions_terrain_aa($terrain_value, $aa_value);
					foreach ($plages_com_terrain_aa as $plage_com_aa_key => $plage_com_aa_value) {
						echo "dernier foreach <br/>";
						if ($plage_com_aa_value['com_jour'] == $jour_value){
							echo "test du jour ok <br/>";
							$obj_heure_plage_debut2 = DateTime::createFromFormat('H:i:s', $plage_com_aa_value['com_heure_debut']);
							$obj_heure_plage_fin2 = DateTime::createFromFormat('H:i:s', $plage_com_aa_value['com_heure_fin']);
							
							//Si la plage déclarée inclue la plage testée, => DELETE (au profit de la nouvelle plage) [6-8] + [2-9] => [2-9]
							if ($obj_heure_plage_debut <= $obj_heure_plage_debut2 AND $obj_heure_plage_fin >= $obj_heure_plage_fin2){
								$req = $db->prepare('DELETE FROM planning_commission WHERE id = :id_com');
								$req->bindValue(":id_com", $plage_com_aa_value['id'], PDO::PARAM_INT);
								$req->execute();
								unset($req);
								echo "delete";
							}
							//Si la nouvelle plage est inclu dans une plage plus grande => on scinde en plusieurs plages [1-9] + [2-5] => [1-2]+[2-5]+[5-9]
							elseif ($obj_heure_plage_debut >= $obj_heure_plage_debut2 AND $obj_heure_plage_fin <= $obj_heure_plage_fin2){

								// On vérifie qu'il s'agisse d'une MAj
								if ($commission == $plage_com_aa_value['com_montant'] OR ($interdiction == 1 AND ( empty($plage_com_aa_value['com_montant']) OR $plage_com_aa_value['com_montant'] == NULL) ) ){
								 	// Pas de MAJ
								 	echo "pas de maj <br/>";
								}
								elseif ($obj_heure_plage_debut == $obj_heure_plage_debut2){
									//[1-9] + [3-9] => [1-3] + [3-9] (commun par la fin)
									// on scinde en deux
									// maj le début l'acienne plage par réduction de la période
									//insert la nouvelle
									echo "même début donc on décale le début de l'ancienne <br/>";
									$req = $db->prepare('
										UPDATE planning_commission
										SET com_heure_debut = :heure_debut
										WHERE id = :id_com
									');
									$req->bindValue(":heure_debut", $heure_fin, PDO::PARAM_STR);
									$req->bindValue(":id_com", $plage_com_aa_value['id'], PDO::PARAM_INT);
									$req->execute();

									unset($req);
								}
								elseif ($obj_heure_plage_fin == $obj_heure_plage_fin2){
									echo "même fin donc on décale la fin  de l'ancienne <br/>";
									//[1-9] + [1-4] => [1-4] + [4-9] (commun par le début)
									// on scinde en deux
									// maj le début l'acienne plage par réduction de la période
									//insert la nouvelle
									$req = $db->prepare('
										UPDATE planning_commission
										SET com_montant = :com, com_heure_fin = :heure_fin
										WHERE id = :id_com
									');
									$req->bindValue(":com", $commission, PDO::PARAM_INT);
									$req->bindValue(":heure_fin", $heure_debut, PDO::PARAM_STR);
									$req->bindValue(":id_com", $plage_com_aa_value['id'], PDO::PARAM_INT);
									$req->execute();

									unset($req);
								}
								else{
									echo "strictement inclu <br/>";
									//[1-9] + [3-5] => [1-3] + [3-5] + [5-9]
									//on supprimme l'ancienne
									//on crée une plage avec le mêmes propriétés que l'ancienne avec début identique à l'ancien et fin = début de la nouvelle plage
									//on crée la nouvelle plage
									//on crée une nouvelle plage avec le mêmes propriétés que l'ancienne. Début = fin de la nouvelle plage et fin = ancienne fin
									
									$req = $db->prepare('DELETE FROM planning_commission WHERE id = :id_com');
									$req->bindValue(":id_com", $plage_com_aa_value['id'], PDO::PARAM_INT);
									$req->execute();
									unset($req);
									
									$req2 = $db->prepare('
										INSERT INTO planning_commission(terrain_id, utilisateur_api_id, com_montant, com_heure_debut, com_heure_fin, com_jour)
										VALUES (:terrain_id, :utilisateur_api_id, :com_montant, :com_heure_debut, :com_heure_fin, :com_jour)
									');
									$req2->bindValue(":terrain_id", $terrain_value, PDO::PARAM_INT);
									$req2->bindValue(":utilisateur_api_id", $aa_value, PDO::PARAM_INT);
									$req2->bindValue(":com_montant", $plage_com_aa_value['com_montant'], PDO::PARAM_INT);
									$req2->bindValue(":com_heure_debut", $plage_com_aa_value['com_heure_debut'], PDO::PARAM_STR);
									$req2->bindValue(":com_heure_fin", $heure_debut, PDO::PARAM_STR);
									$req2->bindValue(":com_jour", $jour_value, PDO::PARAM_INT);
									$req2->execute();

									$req2 = $db->prepare('
										INSERT INTO planning_commission(terrain_id, utilisateur_api_id, com_montant, com_heure_debut, com_heure_fin, com_jour)
										VALUES (:terrain_id, :utilisateur_api_id, :com_montant, :com_heure_debut, :com_heure_fin, :com_jour)
									');
									$req2->bindValue(":terrain_id", $terrain_value, PDO::PARAM_INT);
									$req2->bindValue(":utilisateur_api_id", $aa_value, PDO::PARAM_INT);
									$req2->bindValue(":com_montant", $plage_com_aa_value['com_montant'], PDO::PARAM_INT);
									$req2->bindValue(":com_heure_debut", $heure_fin, PDO::PARAM_STR);
									$req2->bindValue(":com_heure_fin", $plage_com_aa_value['com_heure_fin'] , PDO::PARAM_STR);
									$req2->bindValue(":com_jour", $jour_value, PDO::PARAM_INT);
									$req2->execute();

									unset($req2);
								}
							}
							//Si chevauchement des plages cas 1 : [3-7] + [1-5] => [1-5] + [5-7]
							elseif ($obj_heure_plage_debut < $obj_heure_plage_debut2 AND $obj_heure_plage_fin < $obj_heure_plage_fin2 AND $obj_heure_plage_fin > $obj_heure_plage_debut2){
									echo "chevauchement 1 <br/>";
									// maj le début l'acienne plage par réduction de la période
									//insert la nouvelle
									$req = $db->prepare('
										UPDATE planning_commission
										SET com_heure_debut = :heure_debut
										WHERE id = :id_com
									');
									$req->bindValue(":heure_debut", $heure_fin, PDO::PARAM_STR);
									$req->bindValue(":id_com", $plage_com_aa_value['id'], PDO::PARAM_INT);
									$req->execute();

									unset($req);
							}
							//Si chevauchement des plages cas 1 : [3-7] + [5-9] => [3-5] + [5-9]
							elseif ($obj_heure_plage_debut > $obj_heure_plage_debut2 AND $obj_heure_plage_debut < $obj_heure_plage_fin2 AND $obj_heure_plage_fin > $obj_heure_plage_fin2){
									echo "chevauchement 2 <br/>";
									// maj le début l'acienne plage par réduction de la période
									//insert la nouvelle
									$req = $db->prepare('
										UPDATE planning_commission
										SET com_heure_fin = :heure_fin
										WHERE id = :id_com
									');
									$req->bindValue(":heure_fin", $heure_debut, PDO::PARAM_STR);
									$req->bindValue(":id_com", $plage_com_aa_value['id'], PDO::PARAM_INT);
									$req->execute();

									unset($req);
							}
						}
					}
					
					$req2 = $db->prepare('
						INSERT INTO planning_commission(terrain_id, utilisateur_api_id, com_montant, com_heure_debut, com_heure_fin, com_jour)
						VALUES (:terrain_id, :utilisateur_api_id, :com_montant, :com_heure_debut, :com_heure_fin, :com_jour)
					');
					$req2->bindValue(":terrain_id", $terrain_value, PDO::PARAM_INT);
					$req2->bindValue(":utilisateur_api_id", $aa_value, PDO::PARAM_INT);
					$req2->bindValue(":com_montant", $commission, PDO::PARAM_INT);
					$req2->bindValue(":com_heure_debut", $heure_debut, PDO::PARAM_STR);
					$req2->bindValue(":com_heure_fin", $heure_fin, PDO::PARAM_STR);
					$req2->bindValue(":com_jour", $jour_value, PDO::PARAM_INT);
					$req2->execute();

					unset($req2);
					
				}
			}
		}
	}
	else {
		return false;
	}
}
function maj_tarifs($heure_debut, $heure_fin, $terrains, $jours, $tarif){
	// $heure_debut et $ heure_fin sont au format H:i:s
	// $terrains et $aa sont des tableaux
	// return false / true
	$db = connexionBdd();
	$obj_heure_plage_debut = DateTime::createFromFormat('H:i:s', $heure_debut);
	$obj_heure_plage_fin = DateTime::createFromFormat('H:i:s', $heure_fin);
	var_dump($obj_heure_plage_debut);
	var_dump($obj_heure_plage_fin);
	var_dump($heure_debut);
	if ($obj_heure_plage_debut < $obj_heure_plage_fin) {
		foreach ($terrains as $terrain_key => $terrain_value) {
			foreach ($jours as $jour_key => $jour_value) {
				$plages_tarifs = liste_tarifs_terrain($terrain_value);
				var_dump($plages_tarifs);
				var_dump($terrain_value);
				foreach ($plages_tarifs as $plage_tarif_key => $plage_tarif_value) {
					echo "dernier foreach <br/>";
					if ($plage_tarif_value['tarif_jour'] == $jour_value){
						echo "test du jour ok <br/>";
						$obj_heure_plage_debut2 = DateTime::createFromFormat('H:i:s', $plage_tarif_value['tarif_heure_debut']);
						$obj_heure_plage_fin2 = DateTime::createFromFormat('H:i:s', $plage_tarif_value['tarif_heure_fin']);
							
						//Si la plage déclarée inclue la plage testée, => DELETE (au profit de la nouvelle plage) [6-8] + [2-9] => [2-9]
						if ($obj_heure_plage_debut <= $obj_heure_plage_debut2 AND $obj_heure_plage_fin >= $obj_heure_plage_fin2){
							$req = $db->prepare('DELETE FROM planning_tarif WHERE id = :id_tarif');
							$req->bindValue(":id_tarif", $plage_tarif_value['id'], PDO::PARAM_INT);
							$req->execute();
							unset($req);
							echo "delete";
						}
						//Si la nouvelle plage est inclu dans une plage plus grande => on scinde en plusieurs plages [1-9] + [2-5] => [1-2]+[2-5]+[5-9]
						elseif ($obj_heure_plage_debut >= $obj_heure_plage_debut2 AND $obj_heure_plage_fin <= $obj_heure_plage_fin2){

								var_dump($obj_heure_plage_fin);
								var_dump($obj_heure_plage_fin2);
								// On vérifie qu'il s'agisse d'une MAj
								if ($tarif == $plage_tarif_value['tarif_tarif']){
								 	// Pas de MAJ
								 	echo "pas de maj <br/>";
								}
								elseif ($obj_heure_plage_debut == $obj_heure_plage_debut2){
									//[1-9] + [3-9] => [1-3] + [3-9] (commun par la fin)
									// on scinde en deux
									// maj le début l'acienne plage par réduction de la période
									//insert la nouvelle
									echo "même début donc on décale le début de l'ancienne <br/>";
									$req = $db->prepare('
										UPDATE planning_tarif
										SET tarif_heure_debut = :heure_debut
										WHERE id = :id
									');
									$req->bindValue(":heure_debut", $heure_fin, PDO::PARAM_STR);
									$req->bindValue(":id", $plage_tarif_value['id'], PDO::PARAM_INT);
									$req->execute();

									unset($req);
									var_dump($heure_debut);
								}
								elseif ($obj_heure_plage_fin == $obj_heure_plage_fin2){
									echo "même fin donc on décale la fin  de l'ancienne <br/>";
									//[1-9] + [1-4] => [1-4] + [4-9] (commun par le début)
									// on scinde en deux
									// maj le début l'acienne plage par réduction de la période
									//insert la nouvelle
									$req = $db->prepare('
										UPDATE planning_tarif
										SET tarif_tarif = :tarif, tarif_heure_fin = :heure_fin
										WHERE id = :id
									');
									$req->bindValue(":tarif", $tarif, PDO::PARAM_INT);
									$req->bindValue(":heure_fin", $heure_debut, PDO::PARAM_STR);
									$req->bindValue(":id", $plage_tarif_value['id'], PDO::PARAM_INT);
									$req->execute();

									unset($req);
								}
								else{
									echo "strictement inclu <br/>";
									//[1-9] + [3-5] => [1-3] + [3-5] + [5-9]
									//on supprimme l'ancienne
									//on crée une plage avec le mêmes propriétés que l'ancienne avec début identique à l'ancien et fin = début de la nouvelle plage
									//on crée la nouvelle plage
									//on crée une nouvelle plage avec le mêmes propriétés que l'ancienne. Début = fin de la nouvelle plage et fin = ancienne fin
									
									$req = $db->prepare('DELETE FROM planning_tarif WHERE id = :id');
									$req->bindValue(":id", $plage_tarif_value['id'], PDO::PARAM_INT);
									$req->execute();
									unset($req);
									
									$req2 = $db->prepare('
										INSERT INTO planning_tarif(terrain_id, tarif_tarif, tarif_heure_debut, tarif_heure_fin, tarif_jour)
										VALUES (:terrain_id, :tarif, :tarif_heure_debut, :tarif_heure_fin, :tarif_jour)
									');
									$req2->bindValue(":terrain_id", $terrain_value, PDO::PARAM_INT);
									$req2->bindValue(":tarif", $plage_tarif_value['tarif_tarif'], PDO::PARAM_INT);
									$req2->bindValue(":tarif_heure_debut", $plage_tarif_value['tarif_heure_debut'], PDO::PARAM_STR);
									$req2->bindValue(":tarif_heure_fin", $heure_debut, PDO::PARAM_STR);
									$req2->bindValue(":tarif_jour", $jour_value, PDO::PARAM_INT);
									$req2->execute();

									$req2 = $db->prepare('
										INSERT INTO planning_tarif(terrain_id, tarif_tarif, tarif_heure_debut, tarif_heure_fin, tarif_jour)
										VALUES (:terrain_id, :tarif, :tarif_heure_debut, :tarif_heure_fin, :tarif_jour)
									');
									$req2->bindValue(":terrain_id", $terrain_value, PDO::PARAM_INT);
									$req2->bindValue(":tarif", $plage_tarif_value['tarif_tarif'], PDO::PARAM_INT);
									$req2->bindValue(":tarif_heure_debut", $heure_fin, PDO::PARAM_STR);
									$req2->bindValue(":tarif_heure_fin", $plage_tarif_value['tarif_heure_fin'] , PDO::PARAM_STR);
									$req2->bindValue(":tarif_jour", $jour_value, PDO::PARAM_INT);
									$req2->execute();

									unset($req2);
								}
						}
						//Si chevauchement des plages cas 1 : [3-7] + [1-5] => [1-5] + [5-7]
						elseif ($obj_heure_plage_debut < $obj_heure_plage_debut2 AND $obj_heure_plage_fin < $obj_heure_plage_fin2 AND $obj_heure_plage_fin > $obj_heure_plage_debut2){
									echo "chevauchement 1 <br/>";
									// maj le début l'acienne plage par réduction de la période
									//insert la nouvelle
									$req = $db->prepare('
										UPDATE planning_tarif
										SET tarif_heure_debut = :heure_debut
										WHERE id = :id
									');
									$req->bindValue(":heure_debut", $heure_fin, PDO::PARAM_STR);
									$req->bindValue(":id", $plage_tarif_value['id'], PDO::PARAM_INT);
									$req->execute();

									unset($req);
						}
						//Si chevauchement des plages cas 1 : [3-7] + [5-9] => [3-5] + [5-9]
						elseif ($obj_heure_plage_debut > $obj_heure_plage_debut2 AND $obj_heure_plage_debut < $obj_heure_plage_fin2 AND $obj_heure_plage_fin > $obj_heure_plage_fin2){
									echo "chevauchement 2 <br/>";
									// maj le début l'acienne plage par réduction de la période
									//insert la nouvelle
									$req = $db->prepare('
										UPDATE planning_tarif
										SET heure_fin = :heure_fin
										WHERE id = :id
									');
									$req->bindValue(":heure_fin", $heure_debut, PDO::PARAM_STR);
									$req->bindValue(":id", $plage_tarif_value['id'], PDO::PARAM_INT);
									$req->execute();

									unset($req);
						}
					}
				}
				$req2 = $db->prepare('
				INSERT INTO planning_tarif(terrain_id, tarif_tarif, tarif_heure_debut, tarif_heure_fin, tarif_jour)
					VALUES (:terrain_id, :tarif_tarif, :tarif_heure_debut, :tarif_heure_fin, :tarif_jour)
				');
				$req2->bindValue(":terrain_id", $terrain_value, PDO::PARAM_INT);
				$req2->bindValue(":tarif_tarif", $tarif, PDO::PARAM_INT);
				$req2->bindValue(":tarif_heure_debut", $heure_debut, PDO::PARAM_STR);
				$req2->bindValue(":tarif_heure_fin", $heure_fin, PDO::PARAM_STR);
				$req2->bindValue(":tarif_jour", $jour_value, PDO::PARAM_INT);
				$req2->execute();
				unset($req2);
			}
		}
	}
	else {
		return false;
	}
}
function ouvrir_fermer_plage($datetime_debut, $datetime_fin, $terrain, $statut_plage){
	// $datetime sont des objets dates
	// $terrains et $aa sont des tableaux
	// return false / true
	$db = connexionBdd();
	$obj_heure_plage_debut = clone($datetime_debut);
	$obj_heure_plage_fin = clone($datetime_fin);
	if ($obj_heure_plage_debut < $obj_heure_plage_fin) {
		$plages = liste_plages_horaires_terrain($terrain);
		$maj = 0;
		foreach ($plages as $plage_key => $plage_value) {
			$obj_heure_plage_debut2 = DateTime::createFromFormat('Y-m-j H:i:s', $plage_value['hor_heure_debut']);
			$obj_heure_plage_fin2 = DateTime::createFromFormat('Y-m-j H:i:s', $plage_value['hor_heure_fin']);
			//Si la maj s'opère sur une résa
			if ($plage_value['statut_id'] == 3){
				// Si la nouvelle plage n'a rien à voir avec la résa
				if ($obj_heure_plage_debut >= $obj_heure_plage_fin2 AND $obj_heure_plage_fin <= $obj_heure_plage_debut2){
					//on test la suivante
				}
				// Si la nouvelle plage est inclu dans une résa, alors on ne fait pas de maj;
				elseif ($obj_heure_plage_debut >= $obj_heure_plage_debut2 AND $obj_heure_plage_fin <= $obj_heure_plage_fin2){
					$maj = 1;
					break 1;
				}
				//chevauchement par la fin de la nouvelle plage
				elseif($obj_heure_plage_debut < $obj_heure_plage_debut2 AND $obj_heure_plage_fin <= $obj_heure_plage_fin2 AND $obj_heure_plage_fin > $obj_heure_plage_debut2){
					//on réduit la fin de la nouvelle plage
					$datetime_fin = $obj_heure_plage_debut2;
					$obj_heure_plage_fin = clone($datetime_fin);
				}
				//chevauchement par le début de la nouvelle plage
				elseif($obj_heure_plage_debut >= $obj_heure_plage_debut2 AND $obj_heure_plage_fin > $obj_heure_plage_fin2 AND $obj_heure_plage_debut < $obj_heure_plage_fin2){
					//on réduit la debut de la nouvelle plage
					$datetime_debut = $obj_heure_plage_fin2;
					$obj_heure_plage_debut = clone($datetime_debut);
				}
				//Si la résa testée est strictement inclu dans la plage déclarée, on scinde la plage en deux et on maj les deux segments
				elseif($obj_heure_plage_debut < $obj_heure_plage_debut2 AND $obj_heure_plage_fin > $obj_heure_plage_fin2){
					ouvrir_fermer_plage($datetime_debut, $obj_heure_plage_debut2, $terrain, $statut_plage);
					ouvrir_fermer_plage($obj_heure_plage_fin2, $datetime_fin, $terrain, $statut_plage);
					$maj = 1;
					break 1;			
				}
			}

			//Si la plage déclarée inclue la plage testée, => DELETE (au profit de la nouvelle plage) [6-8] + [2-9] => [2-9]
			elseif ($obj_heure_plage_debut <= $obj_heure_plage_debut2 AND $obj_heure_plage_fin >= $obj_heure_plage_fin2){
				delete_plage($plage_value['id']);
			}
			//Si la nouvelle plage est inclu dans une plage plus grande et de mm statut on ne fait rien
			elseif ($obj_heure_plage_debut >= $obj_heure_plage_debut2 AND $obj_heure_plage_fin <= $obj_heure_plage_fin2 AND $statut_plage == $plage_value['statut_id']){
				$maj = 1;
				break 1;
			}
			//Si la nouvelle plage est inclu dans une plage plus grande et de statut différent => on scinde en plusieurs plages [1-9] + [2-5] => [1-2]+[2-5]+[5-9] 
			elseif ($obj_heure_plage_debut >= $obj_heure_plage_debut2 AND $obj_heure_plage_fin <= $obj_heure_plage_fin2 AND $statut_plage != $plage_value['statut_id']){

				if ($obj_heure_plage_debut == $obj_heure_plage_debut2){
					//[1-9] + [3-9] => [1-3] + [3-9] (commun par la fin)
					// on scinde en deux
				 	// maj le début l'acienne plage par réduction de la période
					echo "même début donc on décale le début de l'ancienne <br/>";
					maj_plage_sans_verif($plage_value['id'], $plage_value['statut_id'], $plage_value['reservation_id'], $datetime_fin, $obj_heure_plage_fin2, $plage_value['terrain_id']);
				}
				elseif ($obj_heure_plage_fin == $obj_heure_plage_fin2){
					echo "même fin donc on décale la fin  de l'ancienne <br/>";
					//[1-9] + [1-4] => [1-4] + [4-9] (commun par le début)
					// on scinde en deux
					// maj le début l'acienne plage par réduction de la période
					maj_plage_sans_verif($plage_value['id'], $plage_value['statut_id'], $plage_value['reservation_id'], $obj_heure_plage_debut2, $datetime_debut, $plage_value['terrain_id']);
				}
				else{
					echo "strictement inclu <br/>";
					//[1-9] + [3-5] => [1-3] + [3-5] + [5-9]
					//on supprimme l'ancienne
					//on crée une plage avec le mêmes propriétés que l'ancienne avec début identique à l'ancien et fin = début de la nouvelle plage
					//on crée une nouvelle plage avec le mêmes propriétés que l'ancienne. Début = fin de la nouvelle plage et fin = ancienne fin
									
					delete_plage($plage_value['id']);
					//insert_plage_sans_verif($plage_value['statut_id'], $plage_value['reservation_id'], $plage_value['hor_heure_debut'], $datetime_debut, $plage_value['terrain_id']);
					insert_plage_sans_verif($plage_value['statut_id'], $plage_value['reservation_id'], $obj_heure_plage_debut2, $datetime_debut, $plage_value['terrain_id']);
					insert_plage_sans_verif($plage_value['statut_id'], $plage_value['reservation_id'], $datetime_fin, $obj_heure_plage_fin2, $plage_value['terrain_id']);
				}
			}
			//Si chevauchement des plages cas 1 : [3-7] + [1-5] => [1-5] + [5-7]
			elseif ($obj_heure_plage_debut < $obj_heure_plage_debut2 AND $obj_heure_plage_fin < $obj_heure_plage_fin2 AND $obj_heure_plage_fin > $obj_heure_plage_debut2){
				echo "chevauchement 1 <br/>";
				// maj le début l'acienne plage par réduction de la période
				maj_plage_sans_verif($plage_value['id'], $plage_value['statut_id'], $plage_value['reservation_id'],  $datetime_fin, $obj_heure_plage_fin2, $plage_value['terrain_id']);
			}
			//Si chevauchement des plages cas 1 : [3-7] + [5-9] => [3-5] + [5-9]
			elseif ($obj_heure_plage_debut > $obj_heure_plage_debut2 AND $obj_heure_plage_debut < $obj_heure_plage_fin2 AND $obj_heure_plage_fin > $obj_heure_plage_fin2){
				echo "chevauchement 2 <br/>";
				// maj le début l'acienne plage par réduction de la période
				maj_plage_sans_verif($plage_value['id'], $plage_value['statut_id'], $plage_value['reservation_id'], $obj_heure_plage_debut2, $datetime_debut, $plage_value['terrain_id']);
			}
		}
		if($maj == 0){
			insert_plage_sans_verif($statut_plage, $reservation_id = NULL, $datetime_debut, $datetime_fin, $terrain);
		}
		//vérifier si des plages peuvent se regrouper avec la nouvelle
	}
	else {
		return false;
	}
}
function maj_plage_sans_verif($id, $statut_id, $reservation_id, $hor_heure_debut, $hor_heure_fin, $terrain_id){
	$db = connexionBdd();
	$req = $db->prepare('
		UPDATE plage_horaire
		SET statut_id = :statut_id, reservation_id = :reservation_id, hor_heure_fin = :hor_heure_fin, hor_heure_debut = :hor_heure_debut, terrain_id = :terrain_id
		WHERE id = :id
	');
	$req->bindValue(":id", $id, PDO::PARAM_INT);
	$req->bindValue(":statut_id", $statut_id, PDO::PARAM_INT);
	$req->bindValue(":reservation_id", $reservation_id, PDO::PARAM_INT);
	$req->bindValue(":hor_heure_debut", $hor_heure_debut->format('Y-m-j H:i:s'), PDO::PARAM_STR);
	$req->bindValue(":hor_heure_fin", $hor_heure_fin->format('Y-m-j H:i:s'), PDO::PARAM_STR);
	$req->bindValue(":terrain_id", $terrain_id, PDO::PARAM_INT);
	$req->execute();

	unset($req);
}
function insert_plage_sans_verif($statut_id, $reservation_id, $hor_heure_debut, $hor_heure_fin, $terrain_id){
	$db = connexionBdd();
	$req = $db->prepare('
		INSERT INTO plage_horaire(statut_id, reservation_id, hor_heure_debut, hor_heure_fin, terrain_id)
		VALUES (:statut_id, :reservation_id, :hor_heure_debut, :hor_heure_fin, :terrain_id)
	');
	$req->bindValue(":statut_id", $statut_id, PDO::PARAM_INT);
	$req->bindValue(":reservation_id", $reservation_id, PDO::PARAM_INT);
	$req->bindValue(":hor_heure_debut", $hor_heure_debut->format('Y-m-j H:i:s'), PDO::PARAM_STR);
	$req->bindValue(":hor_heure_fin", $hor_heure_fin->format('Y-m-j H:i:s'), PDO::PARAM_STR);
	$req->bindValue(":terrain_id", $terrain_id, PDO::PARAM_INT);
	$req->execute();
}
function delete_plage($id){
	$db = connexionBdd();
	$req = $db->prepare('DELETE FROM plage_horaire WHERE id = :id');
	$req->bindValue(":id", $id, PDO::PARAM_INT);
	$req->execute();
}
function reserver($datetime_debut, $datetime_fin, $terrain){
	$db = connexionBdd();
	$obj_heure_plage_debut = clone($datetime_debut);
	$obj_heure_plage_fin = clone($datetime_fin);
	if ($obj_heure_plage_debut < $obj_heure_plage_fin) {
		$plages = liste_plages_horaires_terrain($terrain);
		$resa_possible = 0;
		foreach ($plages as $plage_key => $plage_value) {
			$obj_heure_plage_debut2 = DateTime::createFromFormat('Y-m-j H:i:s', $plage_value['hor_heure_debut']);
			$obj_heure_plage_fin2 = DateTime::createFromFormat('Y-m-j H:i:s', $plage_value['hor_heure_fin']);
			if($plage_value['statut_id'] == 1 AND $obj_heure_plage_debut >= $obj_heure_plage_debut2 AND $obj_heure_plage_fin <= $obj_heure_plage_fin2){
				if($obj_heure_plage_debut == $obj_heure_plage_debut2 AND $obj_heure_plage_fin == $obj_heure_plage_fin2){
					delete_plage($plage_value['id']);
					$resa_possible = 1;
					break 1;
				}
				elseif($plage_value['statut_id'] == 1 AND $obj_heure_plage_debut == $obj_heure_plage_debut2 AND $obj_heure_plage_fin < $obj_heure_plage_fin2){
					echo "maj par décalage du début de la plage dispo";
					maj_plage_sans_verif($plage_value['id'], $plage_value['statut_id'], $plage_value['reservation_id'], $datetime_fin, $obj_heure_plage_fin2, $plage_value['terrain_id']);
					$resa_possible = 1;
					break 1;
				}
				elseif($plage_value['statut_id'] == 1 AND $obj_heure_plage_debut > $obj_heure_plage_debut2 AND $obj_heure_plage_fin == $obj_heure_plage_fin2){
					echo "maj par réduction de la fin de la plage";
					maj_plage_sans_verif($plage_value['id'], $plage_value['statut_id'], $plage_value['reservation_id'], $obj_heure_plage_debut2, $datetime_debut, $plage_value['terrain_id']);
					$resa_possible = 1;
					break 1;
				}
				elseif($plage_value['statut_id'] == 1 AND $obj_heure_plage_debut > $obj_heure_plage_debut2 AND $obj_heure_plage_fin < $obj_heure_plage_fin2){
					//On scinde la plage dispo en deux plages, une avant la résa et une aprés la résa
					delete_plage($plage_value['id']);
					insert_plage_sans_verif($plage_value['statut_id'], $plage_value['reservation_id'], $obj_heure_plage_debut2, $datetime_debut, $plage_value['terrain_id']);
					insert_plage_sans_verif($plage_value['statut_id'], $plage_value['reservation_id'], $datetime_fin, $obj_heure_plage_fin2, $plage_value['terrain_id']);
					$resa_possible = 1;
					break 1;
				}
			}
		}
		if ($resa_possible == 1){
			$res_reference = 1;
			$res_est_confirmee = 0;
			$req = $db->prepare('
				INSERT INTO reservation(res_reference, res_est_confirmee)
				VALUES (:res_reference, :res_est_confirmee)
			');
			$req->bindValue(":res_reference", $res_reference, PDO::PARAM_INT);
			$req->bindValue(":res_est_confirmee", $res_est_confirmee, PDO::PARAM_INT);
			$req->execute();

			$req2 = $db->prepare('SELECT * FROM reservation WHERE res_reference = :res_reference AND res_est_confirmee =:res_est_confirmee ORDER BY id DESC');
			$req2->bindValue(":res_reference", $res_reference, PDO::PARAM_INT);
			$req2->bindValue(":res_est_confirmee", $res_est_confirmee, PDO::PARAM_INT);
			$req2->execute();
			$res2 = $req2->fetch();

			insert_plage_sans_verif(3, $res2['id'], $datetime_debut, $datetime_fin, $terrain);
			return "pas de pb, reste à verif la résa";
		}
		else{
			return "resa impossible";
		}
	}
}


?>