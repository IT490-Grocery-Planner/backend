#!/usr/bin/php
<?php
// Send email code
$ROOT = "/home/it490/git/IT490-backend";
require_once("$ROOT/sql/dbConnection.php"); //establishes connection to database

function getGroceriesNearExp() {
    $mydb = dbConnection();
	$query = "SELECT email,item, buyDate FROM Groceries WHERE expirationDate - UNIX_TIMESTAMP(NOW()) <= 86400 AND expirationDate - UNIX_TIMESTAMP(NOW()) > 0 AND buyDate != 0";
	$result = $mydb->query($query);
	$groceries = $result->fetch_all(MYSQLI_ASSOC);
    
    $expirations = array();
    
    foreach($groceries as $grocery){
        $alertItem = ["item" => $grocery['item'], "buyDate" =>  date('m/d/Y', $grocery['buyDate'])];
        if(array_key_exists($grocery['email'], $expirations)){
            array_push($expirations[$grocery['email']], );
        } else {
            $expirations[$grocery['email']] = [$alertItem];
        }
    }
    
    foreach($expirations as $email => $groceryItems) { 

        $rows = "";
        foreach($groceryItems as $item){
            $rows .= " <tr><th>".$item["item"]."</th><th>".$item["buyDate"]."</th></tr>";
        }

        $subject = "Alert: Groceries Near Expiration";
        $message ="
            <html>
            <body>
            <h4>Items that will expire tomorrow</h4>
            <table>
                <tr>
                    <th>Fridge Item</th>
                    <th>Bought</th>
                </tr>
                $rows
            </table>
            </body>
            </html>
        ";

        echo "sending alert to $email...\n";

        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: gc348@njit.edu";

        mail($email, $subject, $message, $headers);
    } 

	
}


getGroceriesNearExp();


?>