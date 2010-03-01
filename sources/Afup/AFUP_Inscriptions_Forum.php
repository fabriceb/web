<?php
define('EURO'                             , '€');

define('AFUP_FORUM_ETAT_CREE'             , 0);
define('AFUP_FORUM_ETAT_ANNULE'           , 1);
define('AFUP_FORUM_ETAT_ERREUR'           , 2);
define('AFUP_FORUM_ETAT_REFUSE'           , 3);
define('AFUP_FORUM_ETAT_REGLE'            , 4);
define('AFUP_FORUM_ETAT_INVITE'           , 5);
define('AFUP_FORUM_ETAT_ATTENTE_REGLEMENT', 6);
define('AFUP_FORUM_ETAT_CONFIRME'         , 7);

define('AFUP_FORUM_FACTURE_A_ENVOYER', 0);
define('AFUP_FORUM_FACTURE_ENVOYEE'  , 1);
define('AFUP_FORUM_FACTURE_RECUE'    , 2);

define('AFUP_FORUM_PREMIERE_JOURNEE'            , 0);
define('AFUP_FORUM_DEUXIEME_JOURNEE'            , 1);
define('AFUP_FORUM_2_JOURNEES'                  , 2);
define('AFUP_FORUM_2_JOURNEES_AFUP'             , 3);
define('AFUP_FORUM_2_JOURNEES_ETUDIANT'         , 4);
define('AFUP_FORUM_2_JOURNEES_PREVENTE'         , 5);
define('AFUP_FORUM_2_JOURNEES_AFUP_PREVENTE'    , 6);
define('AFUP_FORUM_2_JOURNEES_ETUDIANT_PREVENTE', 7);
define('AFUP_FORUM_2_JOURNEES_COUPON'           , 8);
define('AFUP_FORUM_ORGANISATION', 9);
define('AFUP_FORUM_SPONSOR', 10);
define('AFUP_FORUM_PRESSE', 11);
define('AFUP_FORUM_CONFERENCIER', 12);
define('AFUP_FORUM_INVITATION', 13);
define('AFUP_FORUM_PROJET', 14);

define('AFUP_FORUM_REGLEMENT_CARTE_BANCAIRE', 0);
define('AFUP_FORUM_REGLEMENT_CHEQUE'        , 1);
define('AFUP_FORUM_REGLEMENT_VIREMENT'      , 2);
define('AFUP_FORUM_REGLEMENT_AUCUN'         , 3);

$AFUP_Tarifs_Forum = array(
                           AFUP_FORUM_INVITATION => 0,
                           AFUP_FORUM_ORGANISATION => 0,
                           AFUP_FORUM_SPONSOR => 0,
                           AFUP_FORUM_PRESSE => 0,
                           AFUP_FORUM_CONFERENCIER => 0,
                           AFUP_FORUM_PROJET => 0,
                           AFUP_FORUM_PREMIERE_JOURNEE => 120,
                           AFUP_FORUM_DEUXIEME_JOURNEE => 120,
                           AFUP_FORUM_2_JOURNEES       => 180,
                           AFUP_FORUM_2_JOURNEES_AFUP  => 120,
                           AFUP_FORUM_2_JOURNEES_ETUDIANT => 120,
                           AFUP_FORUM_2_JOURNEES_PREVENTE       => 160,
                           AFUP_FORUM_2_JOURNEES_AFUP_PREVENTE  => 100,
                           AFUP_FORUM_2_JOURNEES_ETUDIANT_PREVENTE => 100,
                           AFUP_FORUM_2_JOURNEES_COUPON => 140);

$AFUP_Tarifs_Forum_Lib = array(
                           AFUP_FORUM_INVITATION => 'Invitation',
                           AFUP_FORUM_ORGANISATION => 'Organisation',
                           AFUP_FORUM_PROJET => 'Projet PHP',
                           AFUP_FORUM_SPONSOR => 'Sponsor',
                           AFUP_FORUM_PRESSE => 'Presse',
                           AFUP_FORUM_CONFERENCIER => 'Conferencier',
                           AFUP_FORUM_PREMIERE_JOURNEE => 'Jour 1 ',
                           AFUP_FORUM_DEUXIEME_JOURNEE => 'Jour 2',
                           AFUP_FORUM_2_JOURNEES       => '2 Jours',
                           AFUP_FORUM_2_JOURNEES_AFUP  =>  '2 Jours AFUP',
                           AFUP_FORUM_2_JOURNEES_ETUDIANT =>  '2 Jours Etudiant',
                           AFUP_FORUM_2_JOURNEES_PREVENTE       =>  '2 Jours prévente',
                           AFUP_FORUM_2_JOURNEES_AFUP_PREVENTE  =>  '2 Jours AFUP prévente',
                           AFUP_FORUM_2_JOURNEES_ETUDIANT_PREVENTE =>  '2 Jours Etudiant prévente',
                           AFUP_FORUM_2_JOURNEES_COUPON =>  '2 Jours avec coupon de réduction');




class AFUP_Inscriptions_Forum
{
    /**
     * Instance de la couche d'abstraction ï¿½ la base de donnï¿½es
     * @var     object
     * @access  private
     */
    var $_bdd;

    /**
     * Constructeur.
     *
     * @param  object    $bdd   Instance de la couche d'abstraction ï¿½ la base de donnï¿½es
     * @access public
     * @return void
     */
    function AFUP_Inscriptions_Forum(&$bdd)
    {
        $this->_bdd = $bdd;
    }

    /**
     * Renvoit les informations concernant une inscription
     *$inscrits =
     * @param  int      $id         Identifiant de la personne
     * @param  string   $champs     Champs ï¿½ renvoyer
     * @access public
     * @return array
     */
    function obtenir($id, $champs = 'i.*')
    {
        $requete  = 'SELECT';
        $requete .= '  ' . $champs . ' ';
        $requete .= 'FROM';
        $requete .= '  afup_inscription_forum i ';
        $requete .= 'LEFT JOIN';
        $requete .= '  afup_facturation_forum f ON i.reference = f.reference ';

        $requete .= 'WHERE i.id=' . $id;
        return $this->_bdd->obtenirEnregistrement($requete);
    }

    /**
     * Renvoie la liste des inscriptions pour lesquels md5(concat('id', 'reference')) = $code_md5 (1er argument)
     * (concaténation des champs 'id' et 'reference', passée à la fonction md5)
     *
     * @param $code_md5 Md5 de la concaténation des champs "id" et "reference"
     * @param string $champs Liste des champs à récupérer en BD
     * @return array
     */
    function obtenirInscription($code_md5, $champs = 'i.*')
    {
      $requete  = "SELECT $champs FROM afup_inscription_forum i " ;
      $requete .= "LEFT JOIN afup_facturation_forum f ON i.reference = f.reference " ;
      $requete .= "WHERE md5(CONCAT(i.id, i.reference)) = '$code_md5'" ;

      return $this->_bdd->obtenirEnregistrement($requete) ;
    }

    public function envoyerEmailConvocation($id_forum)
    {
        require_once 'afup/AFUP_Configuration.php';
        $configuration = $GLOBALS['AFUP_CONF'];

        $requete  = 'SELECT';
        $requete .= '  i.*, f.societe, md5(CONCAT(i.id, i.reference)) as md5key ';
        $requete .= 'FROM';
        $requete .= '  afup_inscription_forum i ';
        $requete .= 'LEFT JOIN';
        $requete .= '  afup_facturation_forum f ON i.reference = f.reference ';
        $requete .= 'WHERE  i.id_forum =' . $id_forum . ' ';
        $requete .= 'ORDER BY i.date';
        $requete .= ' LIMIT 50000';
        $inscris  = $this->_bdd->obtenirTous($requete);


        require_once 'phpmailer/class.phpmailer.php';
        foreach ($inscris as $nb => $personne)
        {
         if ($nb%100 == 0)
         {
           sleep(5);
         }
        $mail = new PHPMailer;
        //$personne['email'] = 'xgorse@elao.com';
        $mail->AddAddress($personne['email'], $personne['prenom'] . " " . $personne['nom']);
        //$mail->AddBCC('bureau@fup.org', 'Bureau');

        $mail->From     = $configuration->obtenir('mails|email_expediteur');
        $mail->FromName = $configuration->obtenir('mails|nom_expediteur');

        if ($configuration->obtenir('mails|serveur_smtp')) {
            $mail->Host     = $configuration->obtenir('mails|serveur_smtp');
            $mail->Mailer   = "smtp";
        } else {
            $mail->Mailer   = "mail";
        }

        $sujet  = "Convocation Forum PHP 2009\n";
        $mail->Subject = $sujet;

        $lien = "http://www.afup.org/pages/forumphp2009/convocation_visiteurs.php?id=".$personne['md5key'];
        $qui = $personne['prenom'] . " " . $personne['nom'];
$corps="Bonjour $qui,

Voici la confirmation de votre participation au forum PHP 2009 :

$lien

Vous y trouverez des informations pratiques. Imprimez-la seulement si vous le souhaitez. Il n'est pas nécessaire de la présenter le jour de votre arrivée au forum ; une simple pièce justifiant votre identité suffira (carte d'identité, permis de conduire ...).

Cordialement,
L'équipe organisatrice";


        $mail->Body = $corps;

        $ok = $mail->Send();
         ;
        }
        return $ok;
    }

  /**
     * Renvoit la liste des inscriptions au forum pour l'impression des badges
     *
     * @param  int   $id_forum         Id du forum
     * @return array
     */
    function obtenirListePourBadges($id_forum   = null )
    {
        $requete  = 'SELECT';
        $requete .= '  i.*, f.societe ';
        $requete .= 'FROM';
        $requete .= '  afup_inscription_forum i ';
        $requete .= 'LEFT JOIN';
        $requete .= '  afup_facturation_forum f ON i.reference = f.reference ';
        $requete .= 'WHERE  i.id_forum =' . $id_forum . ' ';
        $requete .= 'ORDER BY i.date';
        //$requete .= ' LIMIT 12';
        return $this->_bdd->obtenirTous($requete);

    }

	/**
     * Renvoit la liste des inscriptions au forum
     *
     * @param  string   $champs         Champs ï¿½ renvoyer
     * @param  string   $ordre          Tri des enregistrements
     * @param  bool     $associatif     Renvoyer un tableau associatif ?
     * @access public
     * @return array
     */
    function obtenirListe($id_forum   = null,
                          $champs     = 'i.*',
                          $ordre      = 'i.date',
                          $associatif = false,
                          $filtre     = false)
    {
        $requete  = 'SELECT';
        $requete .= '  ' . $champs . ' ';
        $requete .= 'FROM';
        $requete .= '  afup_inscription_forum i ';
        $requete .= 'LEFT JOIN';
        $requete .= '  afup_facturation_forum f ON i.reference = f.reference ';
        $requete .= 'WHERE 1=1 ';
        $requete .= '  AND i.id_forum =' . $id_forum . ' ';
        if ($filtre) {
            $requete .= 'i.nom LIKE \'%' . $filtre . '%\' ';
            $requete .= 'OR f.societe LIKE \'%' . $filtre . '%\' ';
        }
        $requete .= 'ORDER BY ' . $ordre;
        if ($associatif) {
            return $this->_bdd->obtenirAssociatif($requete);
        } else {
            return $this->_bdd->obtenirTous($requete);
        }
    }

	function ajouterInscription($id_forum, $reference, $type_inscription, $civilite, $nom, $prenom,
	                            $email, $telephone, $coupon, $citer_societe, $newsletter_afup,
                                $newsletter_nexen, $commentaires =null,
                                $etat = AFUP_FORUM_ETAT_CREE, $facturation = AFUP_FORUM_FACTURE_A_ENVOYER)
    {
        $requete  = 'INSERT INTO ';
        $requete .= '  afup_inscription_forum (id_forum, date, reference, type_inscription, montant,
                               civilite, nom, prenom, email, telephone, coupon, citer_societe,
                               newsletter_afup, newsletter_nexen, commentaires, etat, facturation) ';
        $requete .= 'VALUES (';
        $requete .= $id_forum                                       . ',';
        $requete .= time()                                          . ',';
        $requete .= $this->_bdd->echapper($reference)               . ',';
        $requete .= $this->_bdd->echapper($type_inscription)        . ',';
        $requete .= $GLOBALS['AFUP_Tarifs_Forum'][$type_inscription]. ',';
        $requete .= $this->_bdd->echapper($civilite)                . ',';
        $requete .= $this->_bdd->echapper($nom)                     . ',';
        $requete .= $this->_bdd->echapper($prenom)                  . ',';
        $requete .= $this->_bdd->echapper($email)                   . ',';
        $requete .= $this->_bdd->echapper($telephone)               . ',';
        $requete .= $this->_bdd->echapper($coupon)                  . ',';
        $requete .= $this->_bdd->echapper($citer_societe)           . ',';
        $requete .= $this->_bdd->echapper($newsletter_afup)         . ',';
        $requete .= $this->_bdd->echapper($newsletter_nexen)        . ',';
        $requete .= $this->_bdd->echapper($commentaires)            . ',';
        $requete .= $etat                                           . ',';
        $requete .= $this->_bdd->echapper($facturation)             . ')';

        return $this->_bdd->executer($requete);
    }

    function modifierInscription($id, $reference, $type_inscription, $civilite, $nom, $prenom,
                                 $email, $telephone, $coupon, $citer_societe, $newsletter_afup,
                                 $newsletter_nexen, $commentaires, $etat, $facturation)
    {
        $requete  = 'UPDATE ';
        $requete .= '  afup_inscription_forum ';
        $requete .= 'SET';
        $requete .= '  reference='               . $this->_bdd->echapper($reference)               . ',';
        $requete .= '  type_inscription='        . $this->_bdd->echapper($type_inscription)        . ',';
        $requete .= '  montant='                 . $GLOBALS['AFUP_Tarifs_Forum'][$type_inscription]. ',';
        $requete .= '  civilite='                . $this->_bdd->echapper($civilite)                . ',';
        $requete .= '  nom='                     . $this->_bdd->echapper($nom)                     . ',';
        $requete .= '  prenom='                  . $this->_bdd->echapper($prenom)                  . ',';
        $requete .= '  email='                   . $this->_bdd->echapper($email)                   . ',';
        $requete .= '  telephone='               . $this->_bdd->echapper($telephone)               . ',';
        $requete .= '  coupon='                  . $this->_bdd->echapper($coupon)                  . ',';
        $requete .= '  citer_societe='           . $this->_bdd->echapper($citer_societe)           . ',';
        $requete .= '  newsletter_afup='         . $this->_bdd->echapper($newsletter_afup)         . ',';
        $requete .= '  newsletter_nexen='        . $this->_bdd->echapper($newsletter_nexen)        . ',';
        $requete .= '  commentaires='            . $this->_bdd->echapper($commentaires)            . ',';
        $requete .= '  etat='                    . $this->_bdd->echapper($etat)                    . ',';
        $requete .= '  facturation='             . $this->_bdd->echapper($facturation)                    . ' ';
        $requete .= 'WHERE';
        $requete .= '  id=' . $id;

		$this->modifierEtatInscription($reference, $etat);

        return $this->_bdd->executer($requete);
    }

	function supprimerInscription($id) {
		$requete = 'DELETE FROM afup_inscription_forum WHERE id=' . $id;
		return $this->_bdd->executer($requete);
	}

	function modifierEtatInscription($reference, $etat)
    {
        $requete   = 'UPDATE afup_inscription_forum ';
        $requete  .= 'SET etat=' . $etat . ' ';
        $requete  .= 'WHERE reference=' . $this->_bdd->echapper($reference);
        $this->_bdd->executer($requete);

        $requete   = 'UPDATE afup_facturation_forum ';
        $requete  .= 'SET etat=' . $etat . ' ';
        $requete  .= 'WHERE reference=' . $this->_bdd->echapper($reference);
        return $this->_bdd->executer($requete);
    }

	function ajouterRappel($email, $id_forum = null)
    {
        if ($id_forum == null) {
			require_once 'afup/AFUP_Forum.php';
			$forum = new AFUP_Forum($this->_bdd);
        	$id_forum = $forum->obtenirDernier();
        }
        $requete   = 'INSERT INTO afup_inscriptions_rappels (email, date, id_forum) VALUES (' . $this->_bdd->echapper($email) . ', ' . time() . ', ' . $id_forum . ')';
        return $this->_bdd->executer($requete);
    }

	function obtenirNombreInscrits($id_forum   = null)
	{
        $statistiques  = $this->obtenirStatistiques($id_forum);
		$nombresInscrits = max($statistiques['premier_jour']['inscrits'], $statistiques['second_jour']['inscrits']);

		return $nombresInscrits;
	}

    function obtenirStatistiques($id_forum   = null)
    {
        $statistiques = array();

        // Premier jour
        $requete  = 'SELECT COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
        $requete .= '  AND etat NOT IN (' . AFUP_FORUM_ETAT_ANNULE . ', ' . AFUP_FORUM_ETAT_ERREUR . ', ' . AFUP_FORUM_ETAT_REFUSE . ') ';
        $requete .= '  AND type_inscription IN (' . AFUP_FORUM_PREMIERE_JOURNEE . ',' . AFUP_FORUM_2_JOURNEES . ',' . AFUP_FORUM_2_JOURNEES_AFUP . ',' . AFUP_FORUM_2_JOURNEES_ETUDIANT . ')';
        $statistiques['premier_jour']['inscrits'] = $this->_bdd->obtenirUn($requete);

        $requete  = 'SELECT COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
        $requete .= '  AND etat IN (' . AFUP_FORUM_ETAT_REGLE . ', ' . AFUP_FORUM_ETAT_INVITE . ', ' . AFUP_FORUM_ETAT_CONFIRME . ') ';
        $requete .= '  AND type_inscription IN (' . AFUP_FORUM_PREMIERE_JOURNEE . ',' . AFUP_FORUM_2_JOURNEES . ',' . AFUP_FORUM_2_JOURNEES_AFUP . ',' . AFUP_FORUM_2_JOURNEES_ETUDIANT . ')';
        $statistiques['premier_jour']['confirmes'] = $this->_bdd->obtenirUn($requete);

        $requete  = 'SELECT COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
        $requete .= '  AND etat = ' . AFUP_FORUM_ETAT_ATTENTE_REGLEMENT . ' ';
        $requete .= '  AND type_inscription IN (' . AFUP_FORUM_PREMIERE_JOURNEE . ',' . AFUP_FORUM_2_JOURNEES . ',' . AFUP_FORUM_2_JOURNEES_AFUP . ',' . AFUP_FORUM_2_JOURNEES_ETUDIANT . ')';
        $statistiques['premier_jour']['en_attente_de_reglement'] = $this->_bdd->obtenirUn($requete);

        // Second jour
        $requete  = 'SELECT COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
        $requete .= '  AND etat NOT IN (' . AFUP_FORUM_ETAT_ANNULE . ', ' . AFUP_FORUM_ETAT_ERREUR . ', ' . AFUP_FORUM_ETAT_REFUSE . ') ';
        $requete .= '  AND type_inscription IN (' . AFUP_FORUM_DEUXIEME_JOURNEE . ',' . AFUP_FORUM_2_JOURNEES . ',' . AFUP_FORUM_2_JOURNEES_AFUP . ',' . AFUP_FORUM_2_JOURNEES_ETUDIANT . ')';
        $statistiques['second_jour']['inscrits'] = $this->_bdd->obtenirUn($requete);

        $requete  = 'SELECT COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
        $requete .= '  AND etat IN (' . AFUP_FORUM_ETAT_REGLE . ', ' . AFUP_FORUM_ETAT_INVITE  . ', ' . AFUP_FORUM_ETAT_CONFIRME . ') ';
        $requete .= '  AND type_inscription IN (' . AFUP_FORUM_DEUXIEME_JOURNEE . ',' . AFUP_FORUM_2_JOURNEES . ',' . AFUP_FORUM_2_JOURNEES_AFUP . ',' . AFUP_FORUM_2_JOURNEES_ETUDIANT . ')';
        $statistiques['second_jour']['confirmes'] = $this->_bdd->obtenirUn($requete);

        $requete  = 'SELECT COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
        $requete .= '  AND etat = ' . AFUP_FORUM_ETAT_ATTENTE_REGLEMENT . ' ';
        $requete .= '  AND type_inscription IN (' . AFUP_FORUM_DEUXIEME_JOURNEE . ',' . AFUP_FORUM_2_JOURNEES . ',' . AFUP_FORUM_2_JOURNEES_AFUP . ',' . AFUP_FORUM_2_JOURNEES_ETUDIANT . ')';
        $statistiques['second_jour']['en_attente_de_reglement'] = $this->_bdd->obtenirUn($requete);

        // Nombre de personnes validï¿½es par type d'inscription
        $requete  = 'SELECT type_inscription, COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
        $requete .= '  AND etat IN (' . AFUP_FORUM_ETAT_REGLE . ', ' . AFUP_FORUM_ETAT_ATTENTE_REGLEMENT . ', ' . AFUP_FORUM_ETAT_INVITE . ') ';
        $requete .= 'GROUP BY type_inscription';
        $statistiques['types_inscriptions']['confirmes'] = $this->_bdd->obtenirAssociatif($requete);

        $requete  = 'SELECT type_inscription, COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
        $requete .= '  AND etat IN (' . AFUP_FORUM_ETAT_REGLE . ', ' . AFUP_FORUM_ETAT_ATTENTE_REGLEMENT . ') ';
        $requete .= 'GROUP BY type_inscription';
        $statistiques['types_inscriptions']['payants'] = $this->_bdd->obtenirAssociatif($requete);

        $requete  = 'SELECT type_inscription, COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
        $requete .= 'AND etat NOT IN (' . AFUP_FORUM_ETAT_ANNULE . ', ' . AFUP_FORUM_ETAT_ERREUR . ', ' . AFUP_FORUM_ETAT_REFUSE . ') ';
        $requete .= 'GROUP BY type_inscription';
        $statistiques['types_inscriptions']['inscrits'] = $this->_bdd->obtenirAssociatif($requete);

        $requete  = 'SELECT type_inscription, COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
        $requete .= '  AND etat IN (' . AFUP_FORUM_ETAT_REGLE . ', ' . AFUP_FORUM_ETAT_ATTENTE_REGLEMENT . ') ';
        $requete .= 'GROUP BY type_inscription';
        $statistiques['types_inscriptions']['payants'] = $this->_bdd->obtenirAssociatif($requete);

        $requete  = 'SELECT concat(type_inscription,\'-\',etat) , COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
       // $requete .= '  AND etat IN (' . AFUP_FORUM_ETAT_REGLE . ', ' . AFUP_FORUM_ETAT_ATTENTE_REGLEMENT . ') ';
        $requete .= 'GROUP BY concat(type_inscription,\'-\',etat)';
        //$statistiques['types_inscriptions']['etat'] = $this->_bdd->obtenirAssociatif($requete);

        $requete  = 'SELECT COUNT(*) ';
        $requete .= 'FROM afup_inscription_forum ';
        $requete .= 'WHERE id_forum =' . $id_forum . ' ';
       // $requete .= '  AND etat IN (' . AFUP_FORUM_ETAT_REGLE . ', ' . AFUP_FORUM_ETAT_ATTENTE_REGLEMENT . ') ';
        $requete .= 'GROUP BY id_forum';
        //$statistiques['types_inscriptions']['total'] = $this->_bdd->obtenirUn($requete);

        return $statistiques;
    }
}
?>