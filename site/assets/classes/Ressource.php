<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Ressource
{
	private $JDA = null;
	private $CFG = null;
	private $res = null;
	private $semID = null;

	function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->res = $JDA->getRequest( "res" );
		$this->semID = $JDA->getSemID();
	}

	public function load()
	{
		if ( isset( $this->res ) && isset( $this->semID ) ) {
			$elements   = null;
			$lessons    = array( );
			$retlessons = array( );

			if ( stripos( $this->res, "VS_" ) === 0 ) {
				$elements                 = $this->getElements( $this->res, $this->semID );
				$retlessons[ "elements" ] = "";
				foreach ( $elements as $k => $v ) {
					$lessons = array_merge( $lessons, $this->getResourcePlan( $v->eid, $this->semID ) );
					if ( $retlessons[ "elements" ] == "" )
						$retlessons[ "elements" ] .= $v->eid;
					else
						$retlessons[ "elements" ] .= ";" . $v->eid;
				}
			} else {
				if ( stripos( $this->res, "RM_" ) === 0 ) {
					$type = "room";
				} else if ( stripos( $this->res, "TR_" ) === 0 ) {
					$type = "doz";
				} else if ( stripos( $this->res, "CL_" ) === 0 ) {
					$type = "class";
				}
				$lessons = $this->getResourcePlan( $this->res, $this->semID, $type );
			}

			if(is_array($lessons))
			foreach ( $lessons as $item ) {
				$key = $item->lid . " " . $item->tpid;
				if ( !isset( $retlessons[ $key ] ) )
					$retlessons[ $key ] = array( );
				$retlessons[ $key ][ "type" ]    = $item->type;
				$retlessons[ $key ][ "id" ]      = $item->id;
				$retlessons[ $key ][ "subject" ] = $item->subject;
				$retlessons[ $key ][ "dow" ]     = $item->dow;
				$retlessons[ $key ][ "block" ]   = $item->block;

				if ( isset( $retlessons[ $key ][ "clas" ] ) ) {
					$arr = explode( " ", $retlessons[ $key ][ "clas" ] );
					if ( !in_array( $item->clas, $arr ) )
						$retlessons[ $key ][ "clas" ] = $retlessons[ $key ][ "clas" ] . " " . $item->clas;
				} else
					$retlessons[ $key ][ "clas" ] = $item->clas;

				if ( isset( $retlessons[ $key ][ "doz" ] ) ) {
					$arr = explode( " ", $retlessons[ $key ][ "doz" ] );
					if ( !in_array( $item->doz, $arr ) )
						$retlessons[ $key ][ "doz" ] = $retlessons[ $key ][ "doz" ] . " " . $item->doz;
				} else
					$retlessons[ $key ][ "doz" ] = $item->doz;

				if ( isset( $retlessons[ $key ][ "room" ] ) ) {
					$arr = explode( " ", $retlessons[ $key ][ "room" ] );
					if ( !in_array( $item->room, $arr ) )
						$retlessons[ $key ][ "room" ] = $retlessons[ $key ][ "room" ] . " " . $item->room;
				} else
					$retlessons[ $key ][ "room" ] = $item->room;

				$retlessons[ $key ][ "category" ] = $item->category;
				$retlessons[ $key ][ "key" ]      = $key;
				$retlessons[ $key ][ "owner" ]    = "gpuntis";
				$retlessons[ $key ][ "showtime" ] = "none";
				$retlessons[ $key ][ "etime" ]    = null;
				$retlessons[ $key ][ "stime" ]    = null;
				$retlessons[ $key ][ "name" ]     = $item->name;
				$retlessons[ $key ][ "desc" ]     = $item->description;
				$retlessons[ $key ][ "cell" ]     = "";
				$retlessons[ $key ][ "css" ]      = "";
				$retlessons[ $key ][ "longname" ] = $item->longname;
			}

			$retlessons = $this->getAllRes( $retlessons, $this->semID );
			return array("success"=>true,"data"=>$retlessons );
		}
	}

	private function getElements( $id, $sid )
	{
		$query = "SELECT eid " . "FROM #__thm_organizer_virtual_schedules_elements " . "WHERE vid = '" . $id . "' " . "AND sid = '" . $sid . "'";
		$ret   = $this->JDA->query( $query );
		return $ret;
	}

	private function getAllRes( $less, $classemesterid )
	{
		foreach ( $less as $lesson ) {
			$query = "SELECT CONCAT(CONCAT(#__thm_organizer_lessons.gpuntisID, ' '), " .
					 "#__thm_organizer_lessonperiods.gpuntisID) AS mykey, " .
					 "#__thm_organizer_classes.id as cid, " .
					 "#__thm_organizer_rooms.id as rid, " .
					 "#__thm_organizer_teachers.id as tid, " .
					 "#__thm_organizer_lessons.type as ltype, " .
					 "#__thm_organizer_periods.day AS dow, " .
					 "#__thm_organizer_periods.period AS block, " .
					 "#__thm_organizer_lessons.name, " .
					 "#__thm_organizer_lessons.alias AS description, " .
					 "#__thm_organizer_lessons.gpuntisID AS id, " .
					 "(SELECT 'cyclic') AS type " .
					 "FROM #__thm_organizer_lessons " .
					 "INNER JOIN #__thm_organizer_lessons_times ON #__thm_organizer_lessons.id = #__thm_organizer_lessons_times.lessonID " .
					 "INNER JOIN #__thm_organizer_periods ON #__thm_organizer_lessons_times.periodID = #__thm_organizer_periods.id " .
					 "INNER JOIN #__thm_organizer_rooms ON #__thm_organizer_lessons_times.roomID = #__thm_organizer_rooms.id " .
					 "INNER JOIN #__thm_organizer_lesson_teachers ON #__thm_organizer_lesson_teachers.lessonID = #__thm_organizer_lessons.id " .
					 "INNER JOIN #__thm_organizer_teachers ON #__thm_organizer_lesson_teachers.teacherID = #__thm_organizer_teachers.id " .
					 "INNER JOIN #__thm_organizer_lesson_classes ON #__thm_organizer_lesson_classes.lessonID = #__thm_organizer_teachers.id " .
					 "INNER JOIN #__thm_organizer_classes ON #__thm_organizer_lesson_classes.classID = #__thm_organizer_classes.id" .
					 "WHERE AND #__thm_organizer_lessons.semesterID = '$classemesterid' AND #__thm_organizer_lessons.gpuntisID IN ('" . $lesson[ "id" ] . "');";

			$ret = $this->JDA->query( $query );

			$lessons = array( );

			if ( isset( $ret ) )
				if ( is_array( $ret ) )
					foreach ( $ret as $v ) {
						$key = $v->mykey;
						if ( !isset( $lessons[ $key ] ) )
							$lessons[ $key ] = array( );
						$lessons[ $key ][ "category" ] = $v->ltype;
						if ( isset( $lessons[ $key ][ "clas" ] ) ) {
							$arr = explode( " ", $lessons[ $key ][ "clas" ] );
							if ( !in_array( $v->cid, $arr ) )
								$lessons[ $key ][ "clas" ] = $lessons[ $key ][ "clas" ] . " " . $v->cid;
						} else
							$lessons[ $key ][ "clas" ] = $v->cid;

						if ( isset( $lessons[ $key ][ "doz" ] ) ) {
							$arr = explode( " ", $lessons[ $key ][ "doz" ] );
							if ( !in_array( $v->tid, $arr ) )
								$lessons[ $key ][ "doz" ] = $lessons[ $key ][ "doz" ] . " " . $v->tid;
						} else
							$lessons[ $key ][ "doz" ] = $v->tid;

						if ( isset( $lessons[ $key ][ "room" ] ) ) {
							$arr = explode( " ", $lessons[ $key ][ "room" ] );
							if ( !in_array( $v->rid, $arr ) )
								$lessons[ $key ][ "room" ] = $lessons[ $key ][ "room" ] . " " . $v->rid;
						} else
							$lessons[ $key ][ "room" ] = $v->rid;

						$lessons[ $key ][ "dow" ]      = $v->dow;
						$lessons[ $key ][ "block" ]    = $v->block;
						$lessons[ $key ][ "name" ]     = $v->name;
						$lessons[ $key ][ "desc" ]     = $v->description;
						$lessons[ $key ][ "cell" ]     = "";
						$lessons[ $key ][ "css" ]      = "";
						$lessons[ $key ][ "owner" ]    = "gpuntis";
						$lessons[ $key ][ "showtime" ] = "none";
						$lessons[ $key ][ "etime" ]    = null;
						$lessons[ $key ][ "stime" ]    = null;
						$lessons[ $key ][ "key" ]      = $key;
						$lessons[ $key ][ "id" ]       = $v->id;
						$lessons[ $key ][ "subject" ]  = $v->id;
						$lessons[ $key ][ "type" ]     = $v->type;
					}

			foreach ( $lessons as $l ) {
				if ( $lesson[ "key" ] == $l[ "key" ] ) {
					$less[ $lesson[ "key" ] ][ "clas" ] = $l[ "clas" ];
					$less[ $lesson[ "key" ] ][ "doz" ]  = $l[ "doz" ];
					$less[ $lesson[ "key" ] ][ "room" ] = $l[ "room" ];
				}
			}
		}
		return $less;
	}

	private function getResourcePlan( $ressourcename, $fachsemester, $type )
	{
		$query = "SELECT " .
				 "#__thm_organizer_lessons.gpuntisID AS lid, " .
				 "#__thm_organizer_periods.gpuntisID AS tpid, " .
				 "#__thm_organizer_lessons.gpuntisID AS id, " .
				 "#__thm_organizer_lessons.alias AS description, " .
				 "#__thm_organizer_lessons.gpuntisID AS subject, " .
				 "#__thm_organizer_lessons.type AS category, " .
				 "#__thm_organizer_lessons.name AS name, " .
				 "#__thm_organizer_classes.gpuntisID AS clas, " .
				 "#__thm_organizer_teachers.gpuntisID AS doz, " .
				 "#__thm_organizer_rooms.gpuntisID AS room, " .
				 "#__thm_organizer_periods.day AS dow, " .
				 "#__thm_organizer_periods.period AS block, " .
				 "(SELECT 'cyclic') AS type, ";

		if ($this->JDA->isComponentavailable("com_giessenlsf"))
		{
			$query .= " modultitel AS longname FROM #__thm_organizer_objects AS lo LEFT JOIN #__giessen_lsf_modules AS mo ON lo.oalias = mo.modulnummer ";
		}
		else
		{
			$query .= " '' AS longname ";
		}
		$query .= "FROM #__thm_organizer_lessons " .
				  "INNER JOIN #__thm_organizer_lessons_times ON #__thm_organizer_lessons.id = #__thm_organizer_lessons_times.lessonID " .
				  "INNER JOIN #__thm_organizer_periods ON #__thm_organizer_lessons_times.periodID = #__thm_organizer_periods.id " .
				  "INNER JOIN #__thm_organizer_rooms ON #__thm_organizer_lessons_times.roomID = #__thm_organizer_rooms.id " .
				  "INNER JOIN #__thm_organizer_lesson_teachers ON #__thm_organizer_lesson_teachers.lessonID = #__thm_organizer_lessons.id " .
				  "INNER JOIN #__thm_organizer_teachers ON #__thm_organizer_lesson_teachers.teacherID = #__thm_organizer_teachers.id " .
				  "INNER JOIN #__thm_organizer_lesson_classes ON #__thm_organizer_lesson_classes.lessonID = #__thm_organizer_teachers.id " .
				  "INNER JOIN #__thm_organizer_classes ON #__thm_organizer_lesson_classes.classID = #__thm_organizer_classes.id " .
	         	  "WHERE #__thm_organizer_lessons.semesterID = '$fachsemester' ".
	              "AND ";
	    if($type === "doz")
	    	$query .= "( #__thm_organizer_classes.gpuntisID = '".$ressourcename."')";
	    else if($type === "room")
	    	$query .= "( #__thm_organizer_rooms.gpuntisID = '".$ressourcename."')";
	    else if(type === "class")
	    	$query .= "( #__thm_organizer_teachers.gpuntisID = '".$ressourcename."')";

		$hits  = $this->JDA->query( $query );
		return $hits;
	}
}
?>