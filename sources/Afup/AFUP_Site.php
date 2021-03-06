<?php
require_once dirname(__FILE__).'/AFUP_Configuration.php';
require_once dirname(__FILE__).'/AFUP_Base_De_Donnees.php';
require_once dirname(__FILE__).'/AFUP_Pagination.php';

class AFUP_Site_Base_De_Donnees extends AFUP_Base_De_Donnees {
    function __construct() {
        $conf = $GLOBALS['AFUP_CONF'];
        parent::AFUP_Base_De_Donnees($conf->obtenir('bdd|hote'),
                                     $conf->obtenir('bdd|base'),
                                     $conf->obtenir('bdd|utilisateur'),
                                     $conf->obtenir('bdd|mot_de_passe'));

    }
}

class AFUP_Site_Page {
    public $route = "";
    public $content;
    public $title;
	public $conf;

    function __construct($bdd=false) {
        if ($bdd) {
            $this->bdd = $bdd;
        } else {
            $this->bdd = new AFUP_Site_Base_De_Donnees();
        }
        $this->conf = $GLOBALS['AFUP_CONF'];
    }

    function definirRoute($route) {
        $this->route = $route;
        switch (true) {
            case preg_match("%\s*/[0-9]*/\s*%", $this->route):
                list(, $id, ) = explode("/", $this->route);
                $article = new AFUP_Site_Article($id, $this->bdd);
                $article->charger();
                $this->title = $article->titre;
                $this->content = $article->afficher();
                break;

            case preg_match("%s*/[0-9]*%", $this->route):
                list(, $id) = explode("/", $this->route);
                $rubrique = new AFUP_Site_Rubrique($id, $this->bdd);
                $rubrique->charger();
                $this->title = $rubrique->nom;
                $this->content = $rubrique->afficher();
                break;

          default:
                $accueil = new AFUP_Site_Accueil($this->bdd);
                $accueil->charger();
                $this->title = "promouvoir le PHP aupr&egrave;s des professionnels";
                $this->content = $accueil->afficher();
        }
    }

    function community() {
    	$branche = new AFUP_Site_Branche($this->bdd);
    	return $branche->naviguer(5, 2);
    }

    function header() {
    	$branche = new AFUP_Site_Branche($this->bdd);
        return $branche->naviguer(21, 2);
    }

    function content() {
        return $this->content;
    }

    function social() {
    	return '<ul id="menufooter-share">
                    <li>
                        <a href="'.$this->conf->obtenir('web|path').$this->conf->obtenir('site|prefix').$this->conf->obtenir('site|query_prefix').'faq/53/comment-contacter-l-afup" class="spriteshare spriteshare-mail">Nous contacter</a>
                    </li>
                    <li>
                        <a href="http://www.facebook.com/fandelafup" class="spriteshare spriteshare-facebook">L\'AFUP sur Facebook</a>
                    </li>
                    <li>
                        <a href="https://twitter.com/afup" class="spriteshare spriteshare-twitter">L\'AFUP sur Twitter</a>
                    </li>
                </ul>
                <a href="'.$this->conf->obtenir('web|path').$this->conf->obtenir('site|prefix').$this->conf->obtenir('site|query_prefix').'faq/6" id="footer-faq">Encore des questions ? <strong>F.A.Q.</strong></a>';
    }

    function footer() {
    	$branche = new AFUP_Site_Branche($this->bdd);
    	return $branche->naviguer(38, 2, "menufooter-top");
	}
}

class AFUP_Site_Accueil {
    function __construct($bdd=false) {
        if ($bdd) {
            $this->bdd = $bdd;
        } else {
            $this->bdd = new AFUP_Site_Base_De_Donnees();
        }
    }

    function charger() {

    }

    function afficher() {
        return $this->colonne_de_gauche().
               $this->colonne_de_droite();
    }

    function colonne_de_gauche() {
        $articles = new AFUP_Site_Articles($this->bdd);
        $derniers_articles = $articles->chargerDerniersAjouts(5);

        $colonne = '<div id="main" class="mod item left content w66 m50 t100">
                    <h1>Promouvoir le PHP auprès des professionnels</h1>';

        $colonne .= '<blockquote>L\'AFUP a avant tout une vocation d\'information, et fournira les éléments clefs
		     qui permettront de choisir PHP selon les véritables besoins et contraintes d\'un projet.</blockquote>
                     <p>L\'AFUP, Association Française des Utilisateurs de PHP est une association loi 1901,
		     dont le principal but est de promouvoir le langage PHP auprès des professionnels et de participer à son développement.
             L\'AFUP a été créée pour répondre à un besoin croissant des entreprises,
		     celui d\'avoir un interlocuteur unique pour répondre à leurs questions sur PHP.
		     Par ailleurs, l\'AFUP offre un cadre de rencontre et de ressources techniques
		     pour les développeurs qui souhaitent faire avancer le langage PHP lui même.</p>';

        foreach ($derniers_articles as $article) {
            $descriptif = ($article->descriptif) ? $article->descriptif : $article->chapeau;
            $colonne .= '<a href="'.$article->route().'" class="article article-teaser">';
            $colonne .= '<time datetime="'.date('Y-m-d', $article->date).'">'.date('d|m|y', $article->date).'</time>';
            $colonne .= '<h2>'.$article->titre.'</h2>';
            $colonne .= '<p>'.strip_tags($descriptif, '<p><strong>').'</p>';
            $colonne .= '</a>';
        }

        $colonne .= '</div>';

        return $colonne;
    }

    function colonne_de_droite() {
        $branche = new AFUP_Site_Branche($this->bdd);
        $branche->navigation_avec_image(true);
        return '<aside id="sidebar-article" class="mod item left w33 m50 t100"><h2>L\'afup<br>organise...</h2>' . $branche->naviguer(1, 2, "externe", "") . '</aside>';
    }
}

class AFUP_Site_Rubriques {
    function __construct($bdd=false) {
        if ($bdd) {
            $this->bdd = $bdd;
        } else {
            $this->bdd = new AFUP_Site_Base_De_Donnees();
        }
    }

    function chargerSousRubriques($id_site_rubrique) {
        $requete  = ' SELECT';
        $requete .= '  * ';
        $requete .= ' FROM';
        $requete .= '  afup_site_rubrique ';
        $requete .= ' WHERE ';
        $requete .= '  id_parent = '.(int)$id_site_rubrique;
        $requete .= ' ORDER BY date ASC';
        $elements = $this->bdd->obtenirTous($requete);

        $rubriques = array();
        if (is_array($elements)) {
	        foreach ($elements as $element) {
	            $rubrique = new AFUP_Site_Rubrique();
	            $rubrique->remplir($element);
		        $rubriques[] = $rubrique;
	        }
        }

        return $rubriques;

    }

    function obtenirListe($champs = '*',
        $ordre = 'titre',
        $associatif = false)
    {
        $requete = 'SELECT';
        $requete .= '  ' . $champs . ' ';
        $requete .= 'FROM';
        $requete .= '  afup_site_rubrique ';
        $requete .= 'ORDER BY ' . $ordre;
        if ($associatif) {
            return $this->bdd->obtenirAssociatif($requete);
        } else {
            return $this->bdd->obtenirTous($requete);
        }
    }
}

class AFUP_Site_Articles {
    function __construct($bdd=false) {
        if ($bdd) {
            $this->bdd = $bdd;
        } else {
            $this->bdd = new AFUP_Site_Base_De_Donnees();
        }
    }

    function obtenirListe($champs = '*',
        $ordre = 'titre',
        $filtre = false,
        $associatif = false)
    {
        $requete = 'SELECT';
        $requete .= '  afup_site_article.' . $champs . ', afup_site_rubrique.nom as nom_rubrique ';
        $requete .= 'FROM';
        $requete .= '  afup_site_article ';
        $requete .= 'INNER JOIN';
        $requete .= '  afup_site_rubrique on afup_site_article.id_site_rubrique = afup_site_rubrique.id ';
        $requete .= 'WHERE 1 = 1 ';
        if ($filtre) {
            $requete .= 'AND (titre LIKE \'%' . $filtre . '%\' ';
            $requete .= 'OR contenu LIKE \'%' . $filtre . '%\') ';
        }
        $requete .= 'ORDER BY ' . $ordre;
        if ($associatif) {
            return $this->bdd->obtenirAssociatif($requete);
        } else {
            return $this->bdd->obtenirTous($requete);
        }
    }

    function chargerArticlesDeRubrique($id_site_rubrique, $rowcount = null) {
        $requete  = ' SELECT';
        $requete .= '  * ';
        $requete .= ' FROM';
        $requete .= '  afup_site_article ';
        $requete .= ' WHERE ';
        $requete .= '  id_site_rubrique = '.(int)$id_site_rubrique;
        $requete .= '  AND etat = 1';
        $requete .= ' ORDER BY date DESC';
        if (is_int($rowcount)) {
        	$requete .= ' LIMIT 0, '.(int)$rowcount;
        }
        $elements = $this->bdd->obtenirTous($requete);

        $articles = array();
        if (is_array($elements)) {
	        foreach ($elements as $element) {
	            $article = new AFUP_Site_Article(null, $this->bdd);
	            $article->remplir($element);
		        $articles[] = $article;
	        }
        }

        return $articles;

    }

    function chargerDerniersAjouts($rowcount = 10) {
        $requete =  ' SELECT';
        $requete .= '  afup_site_article.* ';
        $requete .= ' FROM';
        $requete .= '  afup_site_article ';
        $requete .= ' INNER JOIN';
        $requete .= '  afup_site_rubrique on afup_site_article.id_site_rubrique = afup_site_rubrique.id';
        $requete .= ' WHERE afup_site_article.etat = 1 ';
        $requete .= ' AND id_parent <> 52 '; // On affiche pas les articles des forums
        $requete .= ' ORDER BY date DESC';
        $requete .= ' LIMIT 0, '.(int)$rowcount;

        $ajouts = array();
        $elements = $this->bdd->obtenirTous($requete);
        foreach ($elements as $element) {
            $article = new AFUP_Site_Article(null, $this->bdd);
            $article->remplir($element);
            $ajouts[] = $article;
        }

        return $ajouts;
    }

    function chargerDernieresQuestions() {
        $requete =  ' SELECT';
        $requete .= '  * ';
        $requete .= ' FROM';
        $requete .= '  afup_site_article ';
        $requete .= ' WHERE id_site_rubrique = 6 ';
        $requete .= ' ORDER BY date DESC';
        $requete .= ' LIMIT 0, 10';

        $questions = array();
        $elements = $this->bdd->obtenirTous($requete);
        foreach ($elements as $element) {
            $article = new AFUP_Site_Article(null, $this->bdd);
            $article->remplir($element);
            $questions[] = $article;
        }

        return $questions;
    }
}

class AFUP_Site_Header {
    public $title;
    public $css = '';
    public $javascript;
    public $rss;

    function __construct() {
        $this->setTitle('promouvoir le PHP aupr&egrave;s des professionnels');
        $this->addCSS('templates/site/css/site.css');
        $this->javascript = '';
        $this->addRSS();
    }

    function setTitle($string) {
        $this->title = '<title>'.$string.' | Association Fran&ccedil;aise des Utilisateurs de PHP (afup.org)</title>';
    }

    function addCSS($file) {
        $conf = $GLOBALS['AFUP_CONF'];
var_dump($file);
die();
        $file = $conf->obtenir('web|path').$file;
        $this->css .= '<link rel="stylesheet" href="'.$file.'" type="text/css" media="all" />';
    }

    function addRSS() {
        $conf = $GLOBALS['AFUP_CONF'];
        $rssFile = $conf->obtenir('web|path').$conf->obtenir('site|prefix').'rss.php';
        $this->rss = '<link rel="alternate" type="application/rss+xml" href="' . $rssFile .'" title="Derni&egraves actus de l\'AFUP"/>';
    }

    function render() {
        return $this->title.$this->css.$this->rss.$this->javascript;
    }
}

class AFUP_Site_Footer {
    private $conf;

    function __construct() {
        $this->conf = $GLOBALS['AFUP_CONF'];
    }

    function logos() {
        $branche = new AFUP_Site_Branche();
        $branche->navigation_avec_image(true);
        return '<ul id="LogoBar"><li>'.$branche->naviguer(9, 0, "LogoElement").'</li></ul>';
    }

    function questions() {
        $articles = new AFUP_Site_Articles();
        $questions = $articles->chargerDernieresQuestions();

        $contenu = '<ul>';
        foreach ($questions as $question) {
            $contenu .= '<li><a href="'.$question->route().'">'.$question->titre().'</a></li>';
        }
        $contenu .= '</ul>';

        return $contenu;
    }

    function articles() {
        $articles = new AFUP_Site_Articles();
        $ajouts = $articles->chargerDerniersAjouts();
        $contenu = '<ul>';
        foreach ($ajouts as $ajout) {
            $contenu .= '<li><a href="'.$ajout->route().'">'.$ajout->titre().'</a></li>';
        }
        $contenu .= '</ul>';

        return $contenu;
    }

	function render() {
		return "";
	}
   }

class AFUP_Site_Branche {
    public $navigation = 'nom';

    function __construct($bdd=false, $conf=false) {
        if ($bdd) {
            $this->bdd = $bdd;
        } else {
            $this->bdd = new AFUP_Site_Base_De_Donnees();
        }
        if ($conf) {
            $this->conf = $conf;
        } else {
            $this->conf = $GLOBALS['AFUP_CONF'];
        }
    }

    function navigation_avec_image($bool = false) {
        if ($bool) {
            $this->navigation = 'image';
        }
    }

    function naviguer($id, $profondeur=1, $identification="") {
        $requete = 'SELECT *
                    FROM afup_site_feuille
                    WHERE id = '.$this->bdd->echapper($id).'
                    AND etat = 1';
        $racine = $this->bdd->obtenirEnregistrement($requete);

        $feuilles = $this->extraireFeuilles($id, $profondeur);
        if ($feuilles) {
            $navigation = '<ul id="' . $identification . '" class="' . AFUP_Site::raccourcir($racine['nom']) . '">' . $feuilles . '</ul>';
        } else {
            $navigation = '';
        }

        return $navigation;
    }

    function extraireFeuilles($id, $profondeur) {
        $extraction = '';

        $requete = 'SELECT *
                    FROM afup_site_feuille
                    WHERE id_parent = '.$this->bdd->echapper($id).'
                    AND etat = 1
                    ORDER BY position';
        $feuilles = $this->bdd->obtenirTous($requete);

        if (is_array($feuilles)) {
	        foreach ($feuilles as $feuille) {
	            $class = "";
	            if ($extraction === "") {
	                $class = ' class="top"';
	            }
	            switch (true) {
	                case preg_match('#^http://#', $feuille['lien']):
	                case preg_match('#^/#', $feuille['lien']):
	                    $route = $feuille['lien'];
	                    break;
	                default:
			            $route = $this->conf->obtenir('web|path').$this->conf->obtenir('site|prefix').$this->conf->obtenir('site|query_prefix').$feuille['lien'];
	                    break;
	            }
	            $extraction .= '<li'.$class.'><a href="'.$route.'" alt="'.$feuille['alt'].'">';
	            if ($this->navigation == 'image') {
                    $extraction .= '<img src="'.$this->conf->obtenir('web|path').'/templates/site/images/'.$feuille['image'].'" />';
	            } else {
	                $extraction .= $feuille['nom'];
	            }
	            $extraction .= '</a></li>';
	            if ($profondeur > 0) {
	                $extraction .= $this->naviguer($feuille['id'], $profondeur - 1);
	            }

	        }
        }

        return $extraction;
    }
}

class AFUP_Site_Feuille {
    public $id;
    public $id_parent;
    public $nom;
    public $lien;
    public $alt;
    public $image;
    public $position;
    public $date;
    public $etat;
    protected $bdd;

    function __construct($id=0, $bdd=false) {
        $this->id = $id;
        if ($bdd) {
            $this->bdd = $bdd;
        } else {
            $this->bdd = new AFUP_Site_Base_De_Donnees();
        }
    }

    function inserer() {
        if ($this->id > 0) {
			$this->supprimer();
		}
        $requete = 'INSERT INTO afup_site_feuille
        			SET
        			id_parent = '.$this->bdd->echapper($this->id_parent).',
        			nom       = '.$this->bdd->echapper($this->nom).',
        			lien      = '.$this->bdd->echapper($this->lien).',
        			alt       = '.$this->bdd->echapper($this->alt).',
        			image     = '.$this->bdd->echapper($this->image).',
        			position  = '.$this->bdd->echapper($this->position).',
        			date      = '.$this->bdd->echapper($this->date).',
        			etat    = '.$this->bdd->echapper($this->etat);
        if ($this->id > 0) {
            $requete .= ', id = '.$this->bdd->echapper($this->id);
        }

        return $this->bdd->executer($requete);
    }

    function modifier() {
        $requete = 'UPDATE afup_site_feuille
        			SET
        			id_parent = '.$this->bdd->echapper($this->id_parent).',
        			nom       = '.$this->bdd->echapper($this->nom).',
        			lien      = '.$this->bdd->echapper($this->lien).',
        			alt       = '.$this->bdd->echapper($this->alt).',
        			image     = '.$this->bdd->echapper($this->image).',
        			position  = '.$this->bdd->echapper($this->position).',
        			date      = '.$this->bdd->echapper($this->date).',
        			etat      = '.$this->bdd->echapper($this->etat).'
        			WHERE id  = '.(int)$this->id;

        return $this->bdd->executer($requete);
    }

    function remplir($f) {
        $this->id = $f['id'];
        $this->id_parent = $f['id_parent'];
        $this->nom = $f['nom'];
        $this->lien = $f['lien'];
        $this->alt = $f['alt'];
        $this->image = $f['image'];
        $this->position = $f['position'];
        $this->date = $f['date'];
        $this->etat = $f['etat'];
    }

    function exportable() {
        return array(
            'id' => $this->id,
        	'id_parent' => $this->id_parent,
        	'nom' => $this->nom,
            'lien' => $this->lien,
            'alt' => $this->alt,
            'image' => $this->image,
            'position' => $this->position,
            'date' => date('Y-m-d', $this->date),
            'etat' => $this->etat,
        );
    }

    function charger() {
        $requete = 'SELECT *
                    FROM afup_site_feuille
                    WHERE id = '.$this->bdd->echapper($this->id);
        $f = $this->bdd->obtenirEnregistrement($requete);
        $this->remplir($f);
    }

    function supprimer() {
        $requete = 'DELETE FROM afup_site_feuille WHERE id = '.$this->bdd->echapper($this->id);
        return $this->bdd->executer($requete);
    }

    function positionable() {
        $positions = array();
        for ($i = 9; $i >= -9; $i--) {
            $positions[$i] = $i;
        }
        return $positions;
    }

}

class AFUP_Site_Feuilles {
    function __construct($bdd=false) {
        if ($bdd) {
            $this->bdd = $bdd;
        } else {
            $this->bdd = new AFUP_Site_Base_De_Donnees();
        }
    }

    function obtenirListe($champs = '*', $ordre = 'titre', $associatif = false) {
		$requete = 'SELECT ' . $champs . ' ';
		$requete .= 'FROM';
		$requete .= '  afup_site_feuille ';
		$requete .= 'ORDER BY ' . $ordre;
		if ($associatif) {
			return $this->bdd->obtenirAssociatif($requete);
		}
		return $this->bdd->obtenirTous($requete);
    }
}


class AFUP_Site_Article {
    public $id;
    public $id_site_rubrique;
    public $id_personne_physique;
    public $surtitre;
    public $titre;
    public $raccourci;
    public $descriptif;
    public $chapeau;
    public $contenu;
    public $position;
    public $date;
    public $etat;
    public $route;

    protected $rubrique;
    protected $bdd;

    function __construct($id = 0, $bdd = false, $conf = false) {
        $this->id = $id;

        if ($bdd) {
            $this->bdd = $bdd;
        } else {
            $this->bdd = new AFUP_Site_Base_De_Donnees();
        }

        if ($conf) {
            $this->conf = $conf;
        } else {
            $this->conf = $GLOBALS['AFUP_CONF'];
        }
    }

    function afficher() {
        return '<article>'.
          	'<time datetime='.date("Y-m-d", $this->date).'>'.$this->date().'</time>'.
          	'<h1>'.$this->titre().'</h1>'.
          	$this->corps().
			'<div class="breadcrumbs">'.$this->fil_d_ariane().'</div>'.
          	'</article>';
    }

    function titre() {
        return $this->titre;
    }

    function teaser() {
    	switch (true) {
    		case !empty($this->chapeau):
    			$teaser = $this->chapeau;
    			break;
    		case !empty($this->descriptif):
    			$teaser = $this->descriptif;
    			break;
    		default:
    			$teaser = substr(strip_tags($this->contenu), 0, 200);
    	}
    	return $teaser;
    }

    function corps() {
        if ($this->etat <= 0) {
            return false;
        }

        $corps = "";
        if (!empty($this->surtitre)) {
            $corps .= '<p class="surtitre">'.$this->surtitre.'</p>';
        }
        if (!empty($this->soustitre)) {
            $corps .= '<h2>'.$this->soustitre.'</h2>';
        }
        if (!empty($this->chapeau)) {
            $corps .= '<blockquote>'.$this->chapeau.'</blockquote>';
        } else {
            $corps .= '<blockquote>'.$this->descriptif.'</blockquote>';
        }
        if (!empty($this->contenu)) {
            $corps .= $this->contenu;
        }

        return $corps;
    }

    function date() {
        return date("d/m/y", $this->date);
    }

    function positionable() {
        $positions = array();
        for ($i = 9; $i >= -9; $i--) {
            $positions[$i] = $i;
        }

        return $positions;
    }

    function exportable() {
        return array(
            'id' => $this->id,
        	'id_site_rubrique' => $this->id_site_rubrique,
        	'id_personne_physique' => $this->id_personne_physique,
            'surtitre' => $this->surtitre,
            'titre' => $this->titre,
            'raccourci' => $this->raccourci,
            'descriptif' => $this->descriptif,
            'chapeau' => $this->chapeau,
            'contenu' => $this->contenu,
            'position' => $this->position,
            'date' => date('Y-m-d', $this->date),
            'etat' => $this->etat,
        );
    }

    function supprimer() {
        $requete = 'DELETE FROM afup_site_article WHERE id = '.$this->bdd->echapper($this->id);
        return $this->bdd->executer($requete);
    }

    function charger() {
        $requete = 'SELECT *
                    FROM afup_site_article
                    WHERE id = '.$this->bdd->echapper($this->id);
        $this->remplir($this->bdd->obtenirEnregistrement($requete));
    }

    function charger_dernier_depuis_rubrique() {
        $requete = 'SELECT *
                    FROM afup_site_article
                    WHERE id_site_rubrique = '.$this->bdd->echapper($this->id_site_rubrique).
                    ' ORDER BY date DESC LIMIT 1';
        $this->remplir($this->bdd->obtenirEnregistrement($requete));
    }

    function remplir($article) {
        $this->id = $article['id'];
        $this->id_site_rubrique = $article['id_site_rubrique'];
        $this->id_personne_physique = $article['id_personne_physique'];
        $this->surtitre = $article['surtitre'];
        $this->titre = $article['titre'];
        $this->raccourci = $article['raccourci'];
        $this->descriptif = $article['descriptif'];
        $this->chapeau = $article['chapeau'];
        $this->contenu = $article['contenu'];
        $this->position = $article['position'];
        $this->date = $article['date'];
        $this->etat = $article['etat'];
        $this->route = $this->route();
    }

    function modifier() {
        $requete = 'UPDATE afup_site_article
        			SET
        			id_site_rubrique      = '.$this->bdd->echapper($this->id_site_rubrique).',
        			id_personne_physique  = '.$this->bdd->echapper($this->id_personne_physique).',
        			surtitre              = '.$this->bdd->echapper($this->surtitre).',
        			titre                 = '.$this->bdd->echapper($this->titre).',
        			raccourci             = '.$this->bdd->echapper($this->raccourci).',
        			descriptif            = '.$this->bdd->echapper($this->descriptif).',
        			chapeau               = '.$this->bdd->echapper($this->chapeau).',
        			contenu               = '.$this->bdd->echapper($this->contenu).',
        			position              = '.$this->bdd->echapper($this->position).',
        			date                  = '.$this->bdd->echapper($this->date).',
        			etat                  = '.$this->bdd->echapper($this->etat).'
                    WHERE
                    id = '.$this->bdd->echapper($this->id);

        return $this->bdd->executer($requete);
    }

    function inserer() {
        $requete = 'INSERT INTO afup_site_article
        			SET
        			id_site_rubrique      = '.$this->bdd->echapper($this->id_site_rubrique).',
        			id_personne_physique  = '.$this->bdd->echapper($this->id_personne_physique).',
        			surtitre              = '.$this->bdd->echapper($this->surtitre).',
        			titre                 = '.$this->bdd->echapper($this->titre).',
        			raccourci             = '.$this->bdd->echapper($this->raccourci).',
        			descriptif            = '.$this->bdd->echapper($this->descriptif).',
        			chapeau               = '.$this->bdd->echapper($this->chapeau).',
        			contenu               = '.$this->bdd->echapper($this->contenu).',
        			position              = '.$this->bdd->echapper($this->position).',
        			date                  = '.$this->bdd->echapper($this->date).',
        			etat                  = '.$this->bdd->echapper($this->etat);
        if ($this->id > 0) {
            $requete .= ', id = '.$this->bdd->echapper($this->id);
        }

        $resultat = $this->bdd->executer($requete);

        if ($resultat) {
            $this->id = $this->bdd->obtenirDernierId();
        }

        return $resultat;
    }

    function route() {
        $rubrique = new AFUP_Site_Rubrique($this->id_site_rubrique, $this->bdd, $this->conf);
        $rubrique->charger();
        if (empty($rubrique->raccourci)) {
            $rubrique->raccourci = 'rubrique';
        }

		return $this->conf->obtenir('web|path').$this->conf->obtenir('site|prefix').$this->conf->obtenir('site|query_prefix').$rubrique->raccourci.'/'.$this->id.'/'.$this->raccourci;
    }

    function fil_d_ariane() {
        $fil = '';

        if ($this->id_site_rubrique > 0) {
            $rubrique = new AFUP_Site_Rubrique($this->id_site_rubrique, $this->bdd, $this->conf);
            $rubrique->charger();
            $fil = $rubrique->fil_d_ariane().$fil;
        }

        return $fil;
    }

    function autres_articles() {
        $articles = new AFUP_Site_Articles($this->bdd);

        return $articles->chargerArticlesDeRubrique($this->id_site_rubrique);
    }
}

class AFUP_Site_Rubrique {
    public $id;
    public $id_personne_physique;
    public $id_parent;
    public $nom;
    public $raccourci;
    public $descriptif;
    public $contenu;
    public $position;
    public $icone;
    public $date;
    public $etat;
    public $pagination;
    protected $bdd;
    protected $conf;
    protected $page_courante;

    function __construct($id = 0, $bdd = false, $conf = false) {
        $this->id = $id;

        if ($bdd) {
            $this->bdd = $bdd;
        } else {
            $this->bdd = new AFUP_Site_Base_De_Donnees();
        }

        if ($conf) {
            $this->conf = $conf;
        } else {
            $this->conf = $GLOBALS['AFUP_CONF'];
        }

        if (isset($_GET['page_courante'])) {
            $this->page_courante = (int)$_GET['page_courante'];
        }
        if ($this->page_courante <= 0) {
            $this->page_courante = 1;
        }
    }

    function afficher() {
        $article = new AFUP_Site_Article(null, $this->bdd);
        $article->id_site_rubrique = $this->id;
        $article->charger_dernier_depuis_rubrique();

        $html_pagination = $this->pagination_html();

        return '<div id="main" class="mod item left content w66 m50 t100">'.
          	'<h1>'.$this->titre().'</h1>'.
         	$this->corps().
            $html_pagination.
			$this->rubriques_dans_la_rubrique().
			$this->articles_dans_la_rubrique().
            $html_pagination.
			'<div class="breadcrumbs">'.$this->fil_d_ariane().'</div>'.
			'</div>';
    }

    function rubrique() {
        return '<ul id="Header">'.
               '<li id="HeaderImg">'.
               $this->image_sous_navigation().
               '</li>'.
               '<li id="HeaderTitle">'.
               $this->titre().
               '</li>'.
               '</ul>';
    }

    function image_sous_navigation() {
        $conf = $GLOBALS['AFUP_CONF'];
        return '<img src="'.$conf->obtenir('web|path').'/templates/site/images/'.$this->icone.'" />';
    }

    function titre() {
        return $this->nom;
    }

    function liste_sous_navigation() {
        $liste = "";

        $sous_rubriques = $this->sous_rubriques();
        if (count($sous_rubriques) > 0) {
            $liste .= '<ul class="Txt NavCategories">';
            foreach ($sous_rubriques as $rubrique) {
                $liste .= '<li><a href="'.$rubrique->route().'">'.$rubrique->nom.'</a></li>';
            }
            $liste .= '</ul>';
        }

        $autres_articles = $this->autres_articles();
        if (count($autres_articles) > 0) {
            $liste .= '<ul class="Txt">';
            foreach ($autres_articles as $article) {
                $liste .= '<li><a href="'.$article->route().'">'.$article->titre.'</a></li>';
            }
            $liste .= '</ul>';
        }

        return $liste;
    }

    function remplir($rubrique) {
        $this->id = $rubrique['id'];
        $this->id_parent = $rubrique['id_parent'];
        $this->id_personne_physique = $rubrique['id_personne_physique'];
        $this->nom = $rubrique['nom'];
        $this->raccourci = $rubrique['raccourci'];
        $this->descriptif = $rubrique['descriptif'];
        $this->contenu = $rubrique['contenu'];
        $this->icone = $rubrique['icone'];
        $this->date = $rubrique['date'];
        $this->etat = $rubrique['etat'];
        $this->pagination = $rubrique['pagination'];
    }

    function charger() {
        $requete = 'SELECT *
                    FROM afup_site_rubrique
                    WHERE id = '.$this->bdd->echapper($this->id);
        $rubrique = $this->bdd->obtenirEnregistrement($requete);
        $this->remplir($rubrique);
    }

    function date() {
        return date("d/m/Y", $this->date);
    }

    function annexe() {
        if ($this->etat <= 0) {
            return false;
        }

        $annexe = '';

        return $annexe;
    }

    function corps() {
        if ($this->etat <= 0) {
            return false;
        }

        $corps = "";
        if (!empty($this->contenu)) {
            $corps .= '<div class="contenu">'.$this->contenu.'</div>';
        }

        return $corps;
    }

    function exportable() {
        return array(
            'id' => $this->id,
        	'id_parent' => $this->id_parent,
        	'id_personne_physique' => $this->id_personne_physique,
            'nom' => $this->nom,
            'raccourci' => $this->raccourci,
            'descriptif' => $this->descriptif,
            'contenu' => $this->contenu,
            'date' => date('Y-m-d', $this->date),
            'etat' => $this->etat,
        );
    }

    function positionable() {
        $positions = array();
        for ($i = 9; $i >= -9; $i--) {
            $positions[$i] = $i;
        }

        return $positions;
    }

    function supprimer() {
        $requete = 'DELETE FROM afup_site_rubrique WHERE id = '.$this->bdd->echapper($this->id);
        return $this->bdd->executer($requete);
    }

    function modifier() {
        $requete = 'UPDATE afup_site_rubrique
        			SET
        			id_parent            = '.$this->bdd->echapper($this->id_parent).',
        			id_personne_physique = '.$this->bdd->echapper($this->id_personne_physique).',
        			position             = '.$this->bdd->echapper($this->position).',
        			date                 = '.$this->bdd->echapper($this->date).',
        			nom                  = '.$this->bdd->echapper($this->nom).',
        			raccourci            = '.$this->bdd->echapper($this->raccourci).',
         			descriptif           = '.$this->bdd->echapper($this->descriptif).',
           			contenu              = '.$this->bdd->echapper($this->contenu).',
           			icone                = '.$this->bdd->echapper($this->icone).',
           			etat                 = '.$this->bdd->echapper($this->etat).'
           			WHERE id             = '.$this->bdd->echapper($this->id);

        return $this->bdd->executer($requete);
    }

    function inserer() {
        $requete = 'INSERT INTO afup_site_rubrique
        			SET
        			id_parent            = '.$this->bdd->echapper($this->id_parent).',
        			id_personne_physique = '.$this->bdd->echapper($this->id_personne_physique).',
        			position             = '.$this->bdd->echapper($this->position).',
        			date                 = '.$this->bdd->echapper($this->date).',
        			nom                  = '.$this->bdd->echapper($this->nom).',
        			raccourci            = '.$this->bdd->echapper($this->raccourci).',
         			descriptif           = '.$this->bdd->echapper($this->descriptif).',
           			contenu              = '.$this->bdd->echapper($this->contenu).',
           			icone                = '.$this->bdd->echapper($this->icone).',
           			etat                 = '.$this->bdd->echapper($this->etat);
        if ($this->id > 0) {
            $requete .= ', id            = '.$this->bdd->echapper($this->id);
        }

        $resultat = $this->bdd->executer($requete);
        if ($resultat) {
            $this->id = $this->bdd->obtenirDernierId();
        }

        return $resultat;
    }

    function route() {
        return $this->conf->obtenir('web|path').$this->conf->obtenir('site|prefix').$this->conf->obtenir('site|query_prefix').$this->raccourci.'/'.$this->id;
    }

    function nom() {
        return $this->nom;
    }

    function fil_d_ariane() {
        $fil = '/ <a href="'.$this->route().'">'.$this->nom.'</a>';

        if ($this->id_parent > 0) {
            $id_parent = $this->id_parent;
            while ($id_parent > 0) {
                $parent = new AFUP_Site_Rubrique($id_parent, $this->bdd, $this->conf);
                $parent->charger();
                $fil = '/ <a href="'.$parent->route().'">'.$parent->nom.'</a> '.$fil;
                $id_parent = $parent->id_parent;
            }
        }

        return $fil;
    }

    function sous_rubriques() {
        $rubriques = new AFUP_Site_Rubriques();
        return $rubriques->chargerSousRubriques($this->id);
    }

    function rubriques_dans_la_rubrique() {
        $sous_rubriques = $this->sous_rubriques();
        $liste = "";
        if (count($sous_rubriques) > 0) {
            $liste = '<ul class="Txt Rubriques">';
            foreach ($sous_rubriques as $rubrique) {
                $liste .= '<li><a href="'.$rubrique->route().'">'.$rubrique->nom.'</a></li>';
            }
            $liste .= '</ul>';
        }

        return $liste;
    }

    function articles_dans_la_rubrique() {
        $autres_articles = $this->autres_articles();
        $articles = "";

        if (count($autres_articles) > 0) {
            foreach ($autres_articles as $article) {
                $articles .= '<a class="article article-teaser" href="'.$article->route().'">'.
                	'<time datetime="'.date("Y-m-d", $article->date).'">'.$article->date().'</time>'.
                	'<h2>'.$article->titre.'</h2>'.
                	'<p>'.$article->teaser().'</p>'.
                	'</a>';
            }
        }

        return $articles;
    }

    function autres_articles() {
        $autres = array();

        $requete  = ' SELECT';
        $requete .= '  * ';
        $requete .= ' FROM';
        $requete .= '  afup_site_article ';
        $requete .= ' WHERE ';
        $requete .= '  etat = 1 ';
        $requete .= ' AND id_site_rubrique = '.(int)$this->id;
        $requete .= ' ORDER BY date DESC';

        if ($this->pagination > 0) {
            $offset = ($this->page_courante-1)*$this->pagination;
            $limit  = $this->pagination;
            $requete .= ' LIMIT '.(int)$offset.', '.$limit;
        }

        $articles = $this->bdd->obtenirTous($requete);

        if (is_array($articles)) {
	        foreach ($articles as $article) {
	            $autre = new AFUP_Site_Article($article['id'], $this->bdd);
	            $autre->remplir($article);
		        $autres[] = $autre;
	        }
        }

        return $autres;
    }

    function compte_autres_articles() {
        $requete  = ' SELECT';
        $requete .= '  COUNT(*) ';
        $requete .= ' FROM';
        $requete .= '  afup_site_article ';
        $requete .= ' WHERE ';
        $requete .= '  etat = 1 ';
        $requete .= ' AND id_site_rubrique = '.(int)$this->id;
        $requete .= ' ORDER BY date DESC';

        return $this->bdd->obtenirUn($requete);
    }

    function pagination_html() {
        if ($this->pagination) {
            return new AFUP_Pagination(
                $this->page_courante,
                $this->pagination,
                $this->compte_autres_articles(),
                array($this, 'genere_route')
            );
        } else {
            return '';
        }
    }

    function genere_route($params) {
        $page = isset($params['page']) ? $params['page'] : 1;
        $url = $this->route();
        if ($page != 1) {
            if (strpos($url, '?') === false) {
                $url.= '?';
            } else {
                $url.= '&';
            }
            $url.= 'page_courante='.$page;
        }

        return $url;
    }
}

class AFUP_Site {
    function __construct($bdd=false) {
        if ($bdd) {
            $this->bdd = $bdd;
        } else {
            $this->bdd = new AFUP_Site_Base_De_Donnees();
        }
    }

    static function raccourcir($texte, $separator='-') {
        $texte = str_replace('�', 'e', $texte);
        $texte = iconv('ISO-8859-15', 'ASCII//TRANSLIT', trim($texte));
        $pattern = array('/[^a-z0-9]/',
                         '/' . $separator . $separator . '+/',
                         '/^' . $separator . '/',
                         '/' . $separator . '$/' );
        $replacement = array($separator, $separator, '', '');
        return preg_replace($pattern, $replacement, strtolower($texte));
    }

	static function transformer_lien_spip($texte) {
		$texte=preg_replace('`\[(.*?)[[:space:]]*->http://(.*?)\]`',"<a href=\"http://".'$2'."\">".'$1'."</a>",$texte);
		$texte=preg_replace('`\[(.*?)->(.*?)\]`',"<a href=\"http://".'$2'."\">".'$1'."</a>",$texte);
		return $texte;
	}

	static function transformer_liste_spip($texte) {
		$lignes = explode("\n", $texte);
		foreach ($lignes as &$ligne) {
			$ligne = preg_replace("`^- (.*)`", "<ul>\n<li>\$1</li>\n</ul>", $ligne);
		}
		$texte = implode("\n", $lignes);
		$texte = str_replace("</ul>\n<ul>\n", '', $texte);
		return $texte;
	}

	static function transformer_spip_en_html($texte) {
		$texte = AFUP_Site::transformer_lien_spip($texte);
		for ($i=0;$i<2;$i++) {
			$texte=preg_replace('`\{\{\{[[:space:]]*(.*?)[[:space:]]*\}\}\}`',"<h3>".'$1'."</h3>",$texte);
			$texte=preg_replace('`\{\{[[:space:]]*(.*?)[[:space:]]*\}\}`',"<strong>".'$1'."</strong>",$texte);
			$texte=preg_replace('`\{[[:space:]]*(.*?)[[:space:]]*\}`',"<em>".'$1'."</em>",$texte);
		}
		$texte = AFUP_Site::transformer_liste_spip($texte);
		return $texte;
	}

    function importer_spip() {
        $this->bdd->executer('TRUNCATE TABLE afup_site_article');
        $this->bdd->executer('TRUNCATE TABLE afup_site_rubrique');

        $requete = 'SELECT * FROM spip_rubriques';
        $rubriques_spip = $this->bdd->obtenirTous($requete);

        $nombre_rubriques = 0;
        foreach ($rubriques_spip as $rubrique_spip) {
            if ($rubrique_spip['statut'] != "prive") {
	            $rubrique = new AFUP_Site_Rubrique($rubrique_spip['id_rubrique']);
	            $rubrique->id_parent = $rubrique_spip['id_parent'];
	            $rubrique->position = 0;
	            $rubrique->date = time();
	            $rubrique->nom = ($rubrique_spip['titre']);
	            $rubrique->raccourci = AFUP_Site::raccourcir($rubrique_spip['titre']);
	            $rubrique->descriptif = AFUP_Site::transformer_spip_en_html(($rubrique_spip['descriptif']));
	            $rubrique->contenu = AFUP_Site::transformer_spip_en_html(($rubrique_spip['texte']));
	            $rubrique->etat = 1;
	            $rubrique->inserer();
	            $nombre_rubriques++;
            }
        }

        $requete = 'SELECT * FROM spip_articles';
        $articles_spip = $this->bdd->obtenirTous($requete);
		$nombre_articles = 0;
        foreach ($articles_spip as $article_spip) {
            if ($article_spip['statut'] == "publie") {
                $article = new AFUP_Site_Article($article_spip['id_article'], $this->bdd);
	            $article->id_site_rubrique = $article_spip['id_rubrique'];
	            $article->surtitre = AFUP_Site::transformer_spip_en_html(($article_spip['surtitre']));
	            $article->titre = ($article_spip['titre']);
	            $article->raccourci = AFUP_Site::raccourcir($article_spip['titre']);
	            $article->descriptif = AFUP_Site::transformer_spip_en_html(($article_spip['descriptif']));
	            $article->chapeau = AFUP_Site::transformer_spip_en_html(($article_spip['chapo']));
	            $article->contenu = AFUP_Site::transformer_spip_en_html(($article_spip['texte']));
	            $article->position = 0;
	            $article->date = strtotime($article_spip['date']);
	            $article->etat = 1;
	            $article->inserer();
	            $nombre_articles++;
            }
        }
    }
}
