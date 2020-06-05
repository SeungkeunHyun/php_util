<?php
class MSSQLFacade {
	private static $dbServer = "localhost";
	private static $connOpts = array(
		"Database" => "InstinctCIGMY"
		, "Uid" => "InstinctSysAdm"
		, "PWD" => "Instinct123"
	);

	/*
	public static function init($svr, $conopt) {
		$this->self["dbserver"] = $svr;
		$this->self["connOpts"] = $conopt;
	}
	*/

	private static function mssql_connect() {
		return sqlsrv_connect(self::$dbServer, self::$connOpts);
	}

	private static function mssql_close($conn) {
		sqlsrv_close($conn);
	}

 	public static function mssql_doQuery($qry) {
		$conn =self::mssql_connect();
		$res = sqlsrv_query($conn, $qry);
		if(!$res) {
			die(print_r(sqlsvr_errors()));
			mssql_close($conn);
			return;
		}
		$arrRes = array();
		while($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
			$arrRes[] = $row;
		}
		sqlsrv_free_stmt($res);
		self::mssql_close($conn);
		return  json_encode($arrRes);
	}

	public static function hello() {
		echo "hello\n";
	}

	public static function test() {
		$jsonDat = MSSQLFacade::mssql_doQuery("Select * from sys.tables");
		print_r($jsonDat);
	}
}
?>
