<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        ModuleMNI
 * @description ModuleMNI component site helper
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

require_once 'lsfapi.php';

/**
 * Class ModuleMNI for component com_thm_organizer
 *
 * Class provides methods to Mapping: LSF-XML Struktur -> Objekt
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class ModuleMNI
{
	/**
	 * XML structure
	 *
	 * @var    Object
	 */
	public $xmlStructure = null;

	/**
	 * Language
	 *
	 * @var    String
	 */
	public $language = null;

	// Atribute der XML-Struktur
	/**
	* Module id
	*
	* @var    String
	*/
	public $modulid = null;

	/**
	 * His number
	 *
	 * @var    String
	 */
	public $nrHis = null;

	/**
	 * Mni number
	 *
	 * @var    String
	 */
	public $nrMni = null;

	/**
	 * Abbreviation
	 *
	 * @var    String
	 */
	public $kuerzel = null;

	/**
	 * Short name
	 *
	 * @var    String
	 */
	public $kurzname = null;

	/**
	 * Short name (german)
	 *
	 * @var    String
	 */
	public $kurzname_de = null;

	/**
	 * Short name (english)
	 *
	 * @var    String
	 */
	public $kurzname_en = null;

	/**
	 * Module title (german)
	 *
	 * @var    String
	 */
	public $modultitelDe = null;

	/**
	 * Module title (english)
	 *
	 * @var    String
	 */
	public $modultitelEn = null;

	/**
	 * Major
	 *
	 * @var    String
	 */
	public $studiengang = null;

	/**
	 * Version
	 *
	 * @var    String
	 */
	public $pversion = null;

	/**
	 * $ktxtppflicht
	 *
	 * @var    String
	 */
	public $ktxtppflicht = null;

	/**
	 * $ktxtpform
	 *
	 * @var    String
	 */
	public $ktxtpform = null;

	/**
	 * Language
	 *
	 * @var    String
	 */
	public $sprache = null;

	/**
	 * Creditpoints
	 *
	 * @var    String
	 */
	public $creditPoints = null;

	/**
	 * Semester week hours
	 *
	 * @var    String
	 */
	public $sws = null;

	/**
	 * Pflichtsemester
	 *
	 * @var    String
	 */
	public $pfsem = null;

	/**
	 * Effort (german)
	 *
	 * @var    String
	 */
	public $aufwand_de = null;

	/**
	 * Effort (english)
	 *
	 * @var    String
	 */
	public $aufwand_en = null;

	/**
	 * Group size
	 *
	 * @var    String
	 */
	public $gruppengr = null;

	/**
	 * Rotation
	 *
	 * @var    String
	 */
	public $turnus = null;

	/**
	 * Duration
	 *
	 * @var    String
	 */
	public $dauer = null;

	/**
	 * $versemester
	 *
	 * @var    String
	 */
	public $versemester = null;

	/**
	 * $verstandbearb
	 *
	 * @var    String
	 */
	public $verstandbearb = null;

	/**
	 * Title (english)
	 *
	 * @var    String
	 */
	public $titelen = null;

	/**
	 * $fgid
	 *
	 * @var    String
	 */
	public $fgid = null;

	/**
	 * Parent modul
	 *
	 * @var    String
	 */
	public $parentmodul = null;

	/**
	 * $stgktxt
	 *
	 * @var    String
	 */
	public $stgktxt = null;

	/**
	 * Graduation
	 *
	 * @var    String
	 */
	public $abschl = null;

	/**
	 * Responsible
	 *
	 * @var    String
	 */
	public $verantworlicher = null;

	/**
	 * Responsible ldap
	 *
	 * @var    String
	 */
	public $verantworlicher_ldap = null;

	/**
	 * Teacher
	 *
	 * @var    String
	 */
	public $dozent = null;

	/**
	 * Teacher ldap
	 *
	 * @var    String
	 */
	public $dozent_ldap = null;

	/**
	 * Short description (german)
	 *
	 * @var    String
	 */
	public $kurzbeschreibung_de = null;

	/**
	 * Short description semester (german)
	 *
	 * @var    String
	 */
	public $shortDescSemDE = null;

	/**
	 * Short description (english)
	 *
	 * @var    String
	 */
	public $kurzbeschreibung_en = null;

	/**
	 * Short description semester (english)
	 *
	 * @var    String
	 */
	public $shortDescSemEN = null;

	/**
	 * Teaching type (german)
	 *
	 * @var    String
	 */
	public $lernform_de = null;

	/**
	 * Teaching type (english)
	 *
	 * @var    String
	 */
	public $lernform_en = null;

	/**
	 * Conditions
	 *
	 * @var    String
	 */
	public $zwvoraussetzungen = null;

	/**
	 * Learning objectives (german)
	 *
	 * @var    String
	 */
	public $lernziel_de = null;

	/**
	 * Learning objectives (english)
	 *
	 * @var    String
	 */
	public $lernziel_en = null;

	/**
	 * Learning content (german)
	 *
	 * @var    String
	 */
	public $lerninhalt_de = null;

	/**
	 * Learning content (english)
	 *
	 * @var    String
	 */
	public $lerninhalt_en = null;

	/**
	 * Preliminary work (german)
	 *
	 * @var    String
	 */
	public $vorleistung_de = null;

	/**
	 * Preliminary work (english)
	 *
	 * @var    String
	 */
	public $vorleistung_en = null;

	/**
	 * Grading (german)
	 *
	 * @var    String
	 */
	public $leistungsnachweis_de = null;

	/**
	 * Grading (english)
	 *
	 * @var    String
	 */
	public $leistungsnachweis_en = null;

	/**
	 * Bibliography
	 *
	 * @var    String
	 */
	public $litverz = null;

	/**
	 * Constructor to set up the client
	 *
	 * @param   <Object>  $xml   XML structure
	 * @param   <String>  $type  Type
	 * @param   <String>  $lang  Language
	 */
	public function __construct($xml, $type = null, $lang = null)
	{
		$this->xmlStructure = $xml;
		if ($type == null)
		{
			$this->parseXmlForDetails();
		}
		else
		{
			$this->parseXmlForList();
		}
		$this->language = $lang;
	}

	/**
	 * Method to set the Language
	 *
	 * @param   String  $lang  Language
	 *
	 * @return void
	 */
	public function setLanguage($lang)
	{
		$this->language = $lang;
	}

	/**
	 * Method to parse the xml for the list
	 *
	 * @return void
	 */
	private function parseXmlForList()
	{
		if (isset($this->xmlStructure->modul))
		{
			$this->xmlStructure = $this->xmlStructure->modul;
		}

		if (isset($this->xmlStructure->modulid))
		{
			$this->modulid = (String) $this->xmlStructure->modulid;
		}
		if (isset($this->xmlStructure->nrhis))
		{
			$this->nrHis = (String) $this->xmlStructure->nrhis;
		}
		if (isset($this->xmlStructure->nrmni))
		{
			$this->nrMni = (String) $this->xmlStructure->nrmni;
		}
		if (isset($this->xmlStructure->kuerzel))
		{
			$this->kuerzel = (String) $this->xmlStructure->kuerzel;
		}
		if (isset($this->xmlStructure->kurzname))
		{
			$this->kurzname = (String) $this->xmlStructure->kurzname;
		}
		if (isset($this->xmlStructure->titelde))
		{
			$this->modultitelDe = (String) $this->xmlStructure->titelde;
		}
		if (isset($this->xmlStructure->titelen))
		{
			$this->modultitelEn = $this->xmlStructure->titelen;
		}
		if (isset($this->xmlStructure->pfsem))
		{
			$this->pfsem = (String) $this->xmlStructure->pfsem;
		}
		if (isset($this->xmlStructure->lp))
		{
			$this->creditPoints = (String) $this->xmlStructure->creditPoints;
		}
		if (isset($this->xmlStructure->ktxtppflicht))
		{
			$this->ktxtppflicht = (String) $this->xmlStructure->ktxtppflicht;
		}
		if (isset($this->xmlStructure->verantwortliche))
		{
			$this->verantworlicher = $this->xmlStructure->verantwortliche->personinfo->vorname . " ";
			$this->verantworlicher .= $this->xmlStructure->verantwortliche->personinfo->nachname;
			$this->verantworlicher_ldap = $this->xmlStructure->verantwortliche->hgnr;
		}

		// Dozenten
		$this->dozent = array();
		if (isset($this->xmlStructure->dozent))
		{
			foreach ($this->xmlStructure->dozent as $dozent)
			{
				$dozentString = isset($dozent->hgnr) ? (String) $dozent->hgnr : (String) $dozent->redmokid;
				$arrPersonInfo = array();
				$arrPersonInfo['id'] = $dozentString;
				$arrPersonInfo['name'] = (String) $dozent->personinfo->{'personal.nachname'};
				$arrPersonInfo['vorname'] = (String) $dozent->personinfo->{'personal.vorname'};
				array_push($this->dozent, $arrPersonInfo);
			}
		}

		$this->xmlStructure = "";
	}

	/**
	 * Method to parse xml for details
	 *
	 * @return void
	 */
	private function parseXmlForDetails()
	{
		if (isset($this->xmlStructure->modul))
		{
			$this->xmlStructure = $this->xmlStructure->modul;
		}

		if (isset($this->xmlStructure->modulid))
		{
			$this->modulid = (String) $this->xmlStructure->modulid;
		}
		if (isset($this->xmlStructure->nrhis))
		{
			$this->nrHis = $this->xmlStructure->nrhis;
		}
		if (isset($this->xmlStructure->nrmni))
		{
			$this->nrMni = $this->xmlStructure->nrmni;
		}
		if (isset($this->xmlStructure->kuerzel))
		{
			$this->kuerzel = $this->xmlStructure->kuerzel;
		}
		if (isset($this->xmlStructure->kurzname))
		{
			$this->kurzname = $this->xmlStructure->kurzname;
		}
		if (isset($this->xmlStructure->titelde))
		{
			$this->modultitelDe = $this->xmlStructure->titelde;
		}
		if (isset($this->xmlStructure->titelen))
		{
			$this->modultitelEn = $this->xmlStructure->titelen;
		}
		if (isset($this->xmlStructure->studiengang))
		{
			$this->studiengang = $this->xmlStructure->studiengang;
		}
		if (isset($this->xmlStructure->pversion))
		{
			$this->pversion = $this->xmlStructure->pversion;
		}
		if (isset($this->xmlStructure->ktxtppflicht))
		{
			$this->ktxtppflicht = $this->xmlStructure->ktxtppflicht;
		}
		if (isset($this->xmlStructure->ktxtpform))
		{
			$this->ktxtpform = $this->xmlStructure->ktxtpform;
		}
		if (isset($this->xmlStructure->ktxtpart))
		{
			$this->ktxtpart = $this->xmlStructure->ktxtpart;
		}
		if (isset($this->xmlStructure->sprache))
		{
			$this->sprache = $this->xmlStructure->sprache;
		}
		if (isset($this->xmlStructure->lp))
		{
			$this->creditPoints = $this->xmlStructure->creditPoints;
		}
		if (isset($this->xmlStructure->sws))
		{
			$this->sws = $this->xmlStructure->sws;
		}
		if (isset($this->xmlStructure->pfsem))
		{
			$this->pfsem = $this->xmlStructure->pfsem;
		}
		if (isset($this->xmlStructure->arbeitsaufwand[0]->txt))
		{
			$this->aufwand_de = $this->xmlStructure->arbeitsaufwand[0]->txt;
		}
		if (isset($this->xmlStructure->arbeitsaufwand[1]->txt))
		{
			$this->aufwand_en = $this->xmlStructure->arbeitsaufwand[1]->txt;
		}

		if (isset($this->xmlStructure->turnus))
		{
			$this->turnus = $this->xmlStructure->turnus;
		}

		if (isset($this->xmlStructure->dauer))
		{
			$this->dauer = $this->xmlStructure->dauer;
		}

		if (isset($this->xmlStructure->versemester))
		{
			$this->versemester = $this->xmlStructure->versemester;
		}
		if (isset($this->xmlStructure->verstandbearb))
		{
			$this->verstandbearb = $this->xmlStructure->verstandbearb;
		}
		if (isset($this->xmlStructure->titelen))
		{
			$this->titelen = $this->xmlStructure->titelen;
		}

		// Modulzuordnung
		if (isset($this->xmlStructure->zuordnung->fgid))
		{
			$this->fgid = $this->xmlStructure->zuordnung->fgid;
		}
		if (isset($this->xmlStructure->zuordnung->parentmodul))
		{
			$this->parentmodul = $this->xmlStructure->zuordnung->parentmodul;
		}
		if (isset($this->xmlStructure->zuordnung->stgktxt))
		{
			$this->stgktxt = $this->xmlStructure->zuordnung->stgktxt;
		}
		if (isset($this->xmlStructure->zuordnung->abschl))
		{
			$this->abschl = $this->xmlStructure->zuordnung->abschl;
		}
		if (isset($this->xmlStructure->zuordnung->pversion))
		{
			$this->pversion = $this->xmlStructure->zuordnung->pversion;
		}

		// Verantwortliche
		if (isset($this->xmlStructure->verantwortliche))
		{
			if (isset($this->xmlStructure->verantwortliche->personinfo))
			{
				$this->verantworlicher = $this->xmlStructure->verantwortliche->personinfo->vorname . " ";
				$this->verantworlicher .= $this->xmlStructure->verantwortliche->personinfo->nachname;
				$this->verantworlicher_ldap = $this->xmlStructure->verantwortliche->hgnr;
			}
		}

		// Dozenten */
		$this->dozent = array();
		if (isset($this->xmlStructure->dozent))
		{
			foreach ($this->xmlStructure->dozent as $dozent)
			{
				$dozentString = isset($dozent->hgnr) ? (String) $dozent->hgnr : (String) $dozent->redmokid;
				$arrPersonInfo = array();
				$arrPersonInfo['id'] = $dozentString;
				$arrPersonInfo['name'] = (String) $dozent->personinfo->{'personal.nachname'};
				$arrPersonInfo['vorname'] = (String) $dozent->personinfo->{'personal.vorname'};
				array_push($this->dozent, $arrPersonInfo);
			}
		}

		// Kurzbeschreibung
		if (isset($this->xmlStructure->kurzbeschr[0]->txt))
		{
			$this->kurzbeschreibung_de = $this->xmlStructure->kurzbeschr[0]->txt;
		}

		if (isset($this->xmlStructure->kurzbeschr[1]->txt))
		{
			$this->kurzbeschreibung_en = $this->xmlStructure->kurzbeschr[1]->txt;
		}

		// Lernform
		if (isset($this->xmlStructure->lernform[0]->txt))
		{
			$this->lernform_de = $this->xmlStructure->lernform[0]->txt;
		}

		if (isset($this->xmlStructure->lernform[1]->txt))
		{
			$this->lernform_en = $this->xmlStructure->lernform[1]->txt;
		}

		// Zulassungsvoraussetzungen
		if (isset($this->xmlStructure->zwvoraussetzungen->txt))
		{
			$this->zwvoraussetzungen = $this->xmlStructure->zwvoraussetzungen->txt;
		}

		// Lernziel
		if (isset($this->xmlStructure->lernziel[0]->txt))
		{
			$this->lernziel_de = $this->xmlStructure->lernziel[0]->txt;
		}

		if (isset($this->xmlStructure->lernziel[1]->txt))
		{
			$this->lernziel_en = $this->xmlStructure->lernziel[1]->txt;
		}

		// Lerninhalt
		if (isset($this->xmlStructure->lerninhalt[0]->txt))
		{
			$this->lerninhalt_de = $this->xmlStructure->lerninhalt[0]->txt;
		}

		if (isset($this->xmlStructure->lerninhalt[1]->txt))
		{
			$this->lerninhalt_en = $this->xmlStructure->lerninhalt[1]->txt;
		}

		// Vorleistung
		if (isset($this->xmlStructure->vorleistung[0]->txt))
		{
			$this->vorleistung_de = $this->xmlStructure->vorleistung[0]->txt;
		}

		if (isset($this->xmlStructure->vorleistung[1]->txt))
		{
			$this->vorleistung_en = $this->xmlStructure->vorleistung[1]->txt;
		}

		// Leistungsnachweis
		if (isset($this->xmlStructure->leistungsnachweis[0]->txt))
		{
			$this->leistungsnachweis_de = $this->xmlStructure->leistungsnachweis[0]->txt;
		}

		if (isset($this->xmlStructure->leistungsnachweis[1]->txt))
		{
			$this->leistungsnachweis_en = $this->xmlStructure->leistungsnachweis[1]->txt;
		}

		// Literaturverzeichnis
		if (isset($this->xmlStructure->litverz->txt))
		{
			$this->litverz = $this->xmlStructure->litverz->txt;
		}
	}

	// --- Getter-Start ---
	/**
	* Method to get the HIS number
	*
	* @return String
	*/
	public function getNrHis()
	{
		return $this->nrHis;
	}

	/**
	 * Method to get the MNI number
	 *
	 * @return String
	 */
	public function getNrMni()
	{
		return $this->nrMni;
	}

	/**
	 * Method to get the abbreviation
	 *
	 * @return String
	 */
	public function getKuerzel()
	{
		return $this->kuerzel;
	}

	/**
	 * Method to get the short name
	 *
	 * @return String
	 */
	public function getKurzname()
	{
		return $this->kurzname;
	}

	/**
	 * Method to get the short name (german)
	 *
	 * @return String
	 */
	public function getKurznameDe()
	{
		return $this->kurzname;
	}

	/**
	 * Method to get the short name (english)
	 *
	 * @return String
	 */
	public function getKurznameEn()
	{
		return $this->kurzname;
	}

	/**
	 * Method to get the module title
	 *
	 * @return String
	 */
	public function getModultitel()
	{
		if ($this->language == "de")
		{
			return $this->modultitelDe;
		}
		else
		{
			return $this->modultitelEn;
		}
	}

	/**
	 * Method to get the module title (german)
	 *
	 * @return String
	 */
	public function getModultitelDe()
	{
		return $this->modultitelDe;
	}

	/**
	 * Method to get the module title (english)
	 *
	 * @return String
	 */
	public function getModultitelEn()
	{
		return $this->modultitelEn;
	}

	/**
	 * Method to get the major
	 *
	 * @return String
	 */
	public function getStudiengang()
	{
		return $this->studiengang;
	}

	/**
	 * Method to get the language
	 *
	 * @return String
	 */
	public function getSprache()
	{
		return $this->sprache;
	}

	/**
	 * Method to get the credit points
	 *
	 * @return String
	 */
	public function getCreditpoints()
	{
		return $this->creditPoints;
	}

	/**
	 * Method to get the effort
	 *
	 * @return String
	 */
	public function getAufwand()
	{
		if ($this->language == "de")
		{
			return $this->aufwand_de;
		}
		else
		{
			return $this->aufwand_en;
		}
	}

	/**
	 * Method to get the rotation
	 *
	 * @return String
	 */
	public function getTurnus()
	{
		if ($this->language == "de")
		{
			return $this->turnus;
		}
		else
		{
			return $this->turnus;
		}
	}

	/**
	 * Method to get the duration
	 *
	 * @return String
	 */
	public function getDauer()
	{
		return $this->dauer;
	}

	/**
	 * Method to get the graduation
	 *
	 * @return String
	 */
	public function getAbschluss()
	{
		return $this->abschl;
	}

	/**
	 * Method to get the responsibles
	 *
	 * @return String
	 */
	public function getModulVerantwortlicher()
	{
		return $this->verantworlicher;
	}

	/**
	 * Method to get the responsibles ldap
	 *
	 * @return String
	 */
	public function getModulVerantwortlicherLdap()
	{
		return $this->verantworlicher_ldap;
	}

	/**
	 * Method to get the short description
	 *
	 * @return String
	 */
	public function getKurzbeschreibung()
	{
		if ($this->language == "de")
		{
			return $this->kurzbeschreibung_de;
		}
		else
		{
			return $this->kurzbeschreibung_en;
		}
	}

	/**
	 * Method to get the teaching type
	 *
	 * @return String
	 */
	public function getLernform()
	{
		if ($this->language == "de")
		{
			return $this->lernform_de;
		}
		else
		{
			return $this->lernform_en;
		}
	}

	/**
	 * Method to get the conditions
	 *
	 * @return String
	 */
	public function getVorraussetzung()
	{
		return $this->zwvoraussetzungen;
	}

	/**
	 * Method to get the learning objects
	 *
	 * @return String
	 */
	public function getLernziel()
	{
		if ($this->language == "de")
		{
			return $this->lernziel_de;
		}
		else
		{
			return $this->lernziel_en;
		}
	}

	/**
	 * Method to get the learning content
	 *
	 * @return String
	 */
	public function getLerninhalt()
	{
		if ($this->language == "de")
		{
			return $this->lerninhalt_de;
		}
		else
		{
			return $this->lerninhalt_en;
		}
	}

	/**
	 * Method to get the preliminary work
	 *
	 * @return String
	 */
	public function getVorleistung()
	{
		if ($this->language == "de")
		{
			return $this->vorleistung_de;
		}
		else
		{
			return $this->vorleistung_en;
		}
	}

	/**
	 * Method to get the grading
	 *
	 * @return String
	 */
	public function getLeistungsnachweis()
	{
		if ($this->language == "de")
		{
			return $this->leistungsnachweis_de;
		}
		else
		{
			return $this->leistungsnachweis_en;
		}
	}

	/**
	 * Method to get the bibliography
	 *
	 * @return String
	 */
	public function getLiteraturVerzeichnis()
	{
		return $this->litverz;
	}

	/**
	 * Method to get the semester weeks hours
	 *
	 * @return String
	 */
	public function getSWS()
	{
		return $this->sws;
	}

	/**
	 * Method to get the semester
	 *
	 * @return String
	 */
	public function getSemester()
	{
		return $this->pfsem;
	}

	/**
	 * Method to get ktxtppflicht
	 *
	 * @return String
	 */
	public function getKtxtppflicht()
	{
		return $this->ktxtppflicht;
	}

	/**
	 * Method to get the modul id
	 *
	 * @return String
	 */
	public function getModulId()
	{
		return $this->modulid;
	}

	/**
	 * Method to get the teacher
	 *
	 * @return String
	 */
	public function getDozenten()
	{
		return $this->dozent;
	}

	// --- Getter-Ende ---
}
