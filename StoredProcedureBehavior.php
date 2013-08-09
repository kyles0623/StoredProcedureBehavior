<?php
class StoredProcedureBehavior extends ModelBehavior {

  /**
	* execute a stored proc. Call executeMssqlSp to not confuse with the cake execute
	* Taken initially from: http://planetcakephp.org/aggregator/items/4390-cakephp-calling-oracle-stored-procedures-and-functions 
	*
	* @param object $Model instance of model
	* @param string $name stored procedure name e.g. run_validation_algorithms_sp
	* @param array $inParams name=>value array of IN params, $type is assigned based on the php variable type
	* @param array $outParams name=>type array of OUTPUT params, 
	*                            e.g. array('algorithm_id' => SQLINT4, 'RETVAL' => SQLVARCHAR).
	*                            $type: SQLTEXT, SQLVARCHAR, SQLCHAR, SQLINT1, SQLINT2, SQLINT4, SQLBIT, SQLFLT4, SQLFLT8, SQLFLTN
	*@return array $output = array('results' => array(), 'params' => $outParams), $outParams has type replaced with the output value
	*/
	function executeMssqlSp(&$Model, $name, $inParams, &$outParams = array(),$source_name='default') {
		// every model has a datasource
		//$dataSource = $Model->getDataSource();

		$dataSource = ConnectionManager::getDataSource($source_name);

		$login =  $dataSource->config['login'];
		$password =  $dataSource->config['password'];
		$serverName =  $dataSource->config['host'];
		$host =  $dataSource->config['host'];
		$database =  $dataSource->config['database'];
		$values = array();

		$connectionInfo = array(
			"Database"=>$database,
			"UID"=>$login,
			"PWD"=>$password);



		$connection = sqlsrv_connect($serverName,$connectionInfo);
		if (!$connection)
		{
			echo "Failed Connection<pre>";
			return -1;
		}

		// Create a new statement
		$sql = " { Call ".$name."( ";

		// IN 
		foreach($inParams as $paramName => $paramValue) {
			$sql .= " ?, ";

			if(is_int($paramValue))
			{
				$paramValue = (int)$paramValue;
				$values[] = array($paramValue,SQLSRV_PARAM_IN,SQLSRV_PHPTYPE_INT);
			}
			else if(is_float($paramValue))
			{
				$paramValue = (float)$paramValue;
				$values[] = array($paramValue,SQLSRV_PARAM_IN,SQLSRV_PHPTYPE_FLOAT);
			}
			else
			{   
				$paramValue = (string)$paramValue;
				$values[] = array($paramValue,SQLSRV_PARAM_IN,SQLSRV_PHPTYPE_STRING( SQLSRV_ENC_CHAR ));
			}
		}



		// OUT

		foreach($outParams as $paramName =>  &$paramValue) {

			//$sql .= $paramName." = ?, ";
			$sql .= " ?, ";


			if(is_int($paramValue))
			{
				$paramValue = (int)$paramValue;
				$values[] = array(&$outParams[$paramName],SQLSRV_PARAM_INOUT,SQLSRV_PHPTYPE_INT);
			}
			else if(is_float($paramValue))
			{
				$paramValue = (float)$paramValue;
				$values[] = array(&$outParams[$paramName],SQLSRV_PARAM_INOUT,SQLSRV_PHPTYPE_FLOAT);
			}
			else
			{   
				$paramValue = (string)$paramValue;
				$values[] = array(&$outParams[$paramName],SQLSRV_PARAM_INOUT,SQLSRV_PHPTYPE_STRING( SQLSRV_ENC_CHAR ),SQLSRV_SQLTYPE_NVARCHAR(255));
			}
			// use what was the param type as the return place holder
		}


		$out= " ";

		$sql = substr($sql, 0, -2);
		$sql .= ") }";

		$cursorType = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
		sqlsrv_configure("WarningsReturnAsErrors", 0);


		$stmt = sqlsrv_query($connection, $sql,$values);
		if($stmt == false)
		{
			return -1;
			//Implement Own Error Statement
			// use sqlsrv_errors() to display errors
		}
		else
		{
			sqlsrv_close($connection);
			return $outParams;
		} 



	}
}
?>
