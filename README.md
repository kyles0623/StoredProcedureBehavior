StoredProcedureBehavior
=======================

Stored Procedure Behavior for CakePHP


Use this for SQL Server Stored Procedure execution. 
It creates and closes its own connection to the server in order to execute the store procedure. 
Using the sqlsrv functionality, the behavior creates a new instance of the connection, does its dirty work and then
returns the output parameters.
