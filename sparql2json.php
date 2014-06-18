<?php
//This PHP file will join the mysql data and semantic data and save to a JSON file


//This function takes care of JSON foramtiing in the output file
function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}

//the the library whihc helps in running sparql queriesries
require_once( "sparqllib.php" );

//connect to the SPARQL end point 
$db = sparql_connect( "http://sparql.vivo.ufl.edu/VIVO/query" );
if(!$db) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }

//All the namespaces required to for the SPARQL query to Run
sparql_ns( "foaf","http://xmlns.com/foaf/0.1/" );
sparql_ns( "rdf","http://www.w3.org/1999/02/22-rdf-syntax-ns#" );
sparql_ns( "rdfs","http://www.w3.org/2000/01/rdf-schema#" );

//name of the Json file . If you runs this script periodically or as a cron job the file nemae will have date when they where created
date_default_timezone_set('America/New_York');
$dte=date('Y-m-d_g a');
 $backupfile1 = 'filename'.$dte.'.json';

//Connect to the mysql database and run the query
$con = mysqli_connect("localhost", "username","password","Db name") or die(mysql_error());
$mysql_result = mysqli_query($con ,"SELECT * FROM table where ");
$contacts=array();
$study_connect =array();

//For each row returned from mysqldata take the praiamry key and run a Sparql query using the primay key value and save that in a array
while($rowsql = mysqli_fetch_array($mysql_result))
{

$temp_id=$rowsql['id'];

if($temp_id!=null)
{
//A sample Sparql Query
$sparql = "SELECT distinct ?Person1 ?DispName ?email ?Person1_ufid  ?DeptName ?primaryEmail ?primaryPhoneNumber 
WHERE{
?Person1 rdf:type foaf:Person .
?Person1 rdfs:label ?tempi_id.
?1 rdfs:label ?DeptName
}";

//Run the SPARQL query 
$result = sparql_query( $sparql ); 

if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }

//the row array will have SPARQL generated data.rowsql will have mysql generated for a primary key store both data into a common array $rows
 while( $row = sparql_fetch_array( $result ) )
{

     $rows['title'] =$rowsql['title'];
     $rows['description'] =$rowsql['description'];
	 $rows['id']=$temp_id;
	 $rows['Display Name']=$row['DispName'];
	 $rows['primaryEmail']=$row['Person1_primaryEmail'];
	 $rows['primaryPhoneNumber']=$row['Person1_primaryPhoneNumber'];
     $rows['keyword1'] =$rowsql['keyword1'];
     $rows['keyword2'] =$rowsql['keyword2'];
     
	 $final_data[]=$rows;
	
}
}
}
//Convert the data in array to JSON
$json=json_encode($final_data);


//Format the JSON
$nice_json =prettyPrint($json);


$fp1 = fopen($backupfile1,"wb");
 //Write the json info
  fwrite($fp1,$nice_json1);
 
    //Close the fp connection
 fclose($fp1);

?>