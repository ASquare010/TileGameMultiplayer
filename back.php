<?php
session_start();

class Player
{
	public $color = "grey";
	public $username ="";
	public $pass ="";
	public $loaction = ""; 
	function __construct($name="",$pass="",$color="",$loc="")
	{
		$this->username = $name;
		$this->pass = $pass;
		$this->color = $color;
		$this->loaction = $loc;
	}
}

//--------------dataBase-----------------------------------------------------

$db_hostname="localhost";
$db_database="game";
$db_username="root";
$db_password="root123";
$connection= mysqli_connect($db_hostname,$db_username,$db_password,$db_database);


//--------------loadPlayerInitialData-----------------------------------------

if(isset($_POST['loadIndex']))	
{
	$p = $_SESSION['currentplayer'];
	$name = $p->username;
	$query = "SELECT * FROM `players` WHERE playerName = '$name'";
	$result = mysqli_query($connection,$query);
	if ($result) 
	{
		$row=	mysqli_fetch_array($result);
		echo json_encode($row);
	}
}
//--------------GameBoxUpdater-----------------------------------------------

if(isset($_POST['index']))	
{
	$query = "SELECT * FROM `grid`";
	$result = mysqli_query($connection,$query);
	if ($result) 
	{
		$row=mysqli_fetch_array($result);
		updateArr($row[0],$_POST['index'],$connection);
	}
}
function updateArr($arr,$val,$connection)
{
	$red = 0;
	$green = 0;
	$yellow = 0;
	$col ="";
	$str="";
	$p = $_SESSION['currentplayer'];
	$plus = explode("+", $arr);

	$queryLoc = "SELECT `location` , `playerColor` FROM `players`";
	$resultLoc = mysqli_query($connection,$queryLoc);
	
	$rows = array();
	
	while ($row = mysqli_fetch_assoc($resultLoc)) {
    	$rows[] = $row;
	}

	for ($i=0; $i < sizeof($rows); $i++) 
	{
		if($rows[$i]['location'] == $val &&  "Red"==$rows[$i]['playerColor'])
		{
			$red++;
		}else if($rows[$i]['location'] == $val &&  "Green"==$rows[$i]['playerColor'])
		{
			$green++;
		}else if($rows[$i]['location'] == $val &&  "Yellow"==$rows[$i]['playerColor'])
		{
			$yellow++;
		}
	}
	if("Red"==$p->color)
		$red++;
	else if("Green"==$p->color)
		$green++;
	else if("Yellow"==$p->color)
		$yellow++;
	
	for ($i=0; $i <sizeof($plus); $i++) 
	{
		$coms = explode(",", $plus[$i]);
		if($coms[0]== $val[0])
		{
			if ($coms[1] == $val[2])
			{
				if ($red > $green && $red > $yellow) {
					$coms[2] = "Red";
					$col = "Red";
			   }else if ($green > $red && $green > $yellow) {
					$coms[2] = "Green";
					$col = "Green";
			   } 
				else if ($yellow > $red && $yellow > $green){
					$coms[2] = "Yellow";
					$col = "Yellow";
			   } else {
					$coms[2] = $p->color;
					$col = $p->color;
				}
			}
		}
			
		$str = $str . $coms[0] . "," . $coms[1] . "," . $coms[2] . "+";
	}
	$str =  rtrim($str, "+");
	$yellow = 0;
	$green = 0;
	$red = 0;
	echo $col;
	$na =$p->username;

	$qur = "UPDATE `players` SET `location` = '$val' WHERE `players`.`playerName` = '$na'";
	mysqli_query($connection,$qur);

	$query = "UPDATE `grid` SET `gridIndex`='$str'";
	mysqli_query($connection,$query);

	functionName($connection, $na, $p->color, "jumpTo :".$val[0].".".$val[2]);


}

//--------------SignUp-------------------------------------------------------

if(isset($_POST['user']))	
{
	$color=$_POST['teamColor'];
	$str = "";
	$plus = array();
	$name=$_POST['user'];
	$pass = $_POST['pass'];
	$queryCheckUser = "SELECT * FROM `players` WHERE playerName='$name'";
	$resultUser = mysqli_query($connection,$queryCheckUser);
	$row=mysqli_fetch_array($resultUser);
	if(!$row )
	{
		$p = new Player($_POST['user'],$_POST['pass'],$_POST['teamColor'],);

		$notFind = false;
		$itr = 0;
		do {
			$x = rand(0,9);
			$y = rand(0,9);
			$q = "SELECT * FROM `players` WHERE location ='$x,$y'";
			$r = mysqli_query($connection,$q);
			$ro = mysqli_fetch_array($r);
			if(!$ro)
			{
				$notFind = true;
			}
			$itr++;
		} while ($notFind == false && $itr > 10);

		$query = "INSERT INTO `players` (`playerName`, `playerPass`, `playerColor`,`location`,`logedIn`) VALUES ('$name', '$pass', '$color','$x,$y','1')";
		$result = mysqli_query($connection,$query);
		$_SESSION['currentplayer'] = $p;
		
		$queryGrid = "SELECT * FROM `grid`";
		$res = mysqli_query($connection,$queryGrid);
		$grid=mysqli_fetch_array($res);
		$plus = explode("+", $grid[0]);
		for ($i=0; $i <sizeof($plus); $i++) 
		{
			$coms = explode(",", $plus[$i]);
			if($coms[0]== "$x" )
			{
				if($coms[1] == "$y")
					$coms[2] = $color;
			}
			$str = $str . $coms[0] . "," . $coms[1] . "," . $coms[2] . "+";
		}
		$str =  rtrim($str, "+");
		$query = "UPDATE `grid` SET `gridIndex`='$str'";
		mysqli_query($connection,$query);
		echo 'true' ;
		functionName($connection, $name, $color, 'NewUser');
	}	
	else{
		echo 'false' ;
	}
	
	
}


//--------------login--------------------------------------------------------

if(isset($_POST['loginPass']))	
{
	$name=$_POST['loginName'];
	$pass = $_POST['loginPass'];
	$query = "SELECT * FROM players WHERE playerName='$name'";
	$result= mysqli_query($connection,$query);
	$row=mysqli_fetch_array($result);
	$updat = "UPDATE `players` SET `logedIn` = '1' WHERE `players`.`playerName` = '$name'";
	mysqli_query($connection,$updat);
	
	if($row)
	{ 
		if($row[1]==$pass)
		{
			$p = new Player($row[0],$row[1],$row[2]);
			$_SESSION['currentplayer'] = $p;
			echo 'true';
			functionName($connection, $p->username, $p->color, 'Login');
		}
		else
			echo 'false';
	}	
	else{
		echo 'false';
	}
}
//--------------logout--------------------------------------------------------



if(isset($_POST['logout']))	
{

	$name=$_SESSION['currentplayer']->username;
	$color=$_SESSION['currentplayer']->color;
	$updat = "UPDATE `players` SET `logedIn` = '0' WHERE `players`.`playerName` = '$name'";
	mysqli_query($connection,$updat);
	echo "true";
	functionName($connection, $name, $color, 'Logout');
}

//--------------Loop----------------------------------------------------------


if(isset($_POST['loop']))	
{

	$queryPlayer = "SELECT * FROM `players`";
	$resultPlayer = mysqli_query($connection,$queryPlayer);

	$queryGrid = "SELECT * FROM `grid`";
	$resultGrid = mysqli_query($connection,$queryGrid);
	$grid=mysqli_fetch_array($resultGrid);

	$queryMessages = "SELECT * FROM `messages`";
	$resultMessages = mysqli_query($connection,$queryMessages);
	$message=mysqli_fetch_array($resultMessages);

	$rows = array();
	$json = array();
	
	while ($row = mysqli_fetch_assoc($resultPlayer)) {
    	$rows[] = $row;
	}

	$message =  rtrim($message[0], "+");
	$json += array("grid" => $grid[0]);
	$json += array("players" => $rows);
	$json += array("messages" => $message);
	echo json_encode($json);
}

//--------------UpdateMessages----------------------------------------------------------
if(isset($_POST['message']))	
{
	$p = $_SESSION['currentplayer'];
	$name=$p->username;
	$color=$p->color;
	$msg = functionName($connection,$name,$color,$_POST['message']);
	echo $msg;
}
function functionName($connection,$name,$color,$msg) {
	$message = $name.','.$color.','.$msg.'+';
	$query = "SELECT * FROM `messages`";
	$result = mysqli_query($connection,$query);
	$row=mysqli_fetch_array($result);
	$update = $row[0].$message;
	$query = "UPDATE `messages` SET `playerMessages`='$update'";
	mysqli_query($connection,$query);
	$update =  rtrim($update, "+");
	return $update;
  }

//--------------toolTip----------------------------------------------------------------
if(isset($_POST['toolTip']))
{
	$val = $_POST['toolTip'];
	$queryCheckUser = "SELECT playerName FROM `players` WHERE location='$val';";
	$resultUser = mysqli_query($connection,$queryCheckUser);
	$rows=array();
	while ($row = mysqli_fetch_assoc($resultUser)) {
		$rows[] = $row;
	}
	if ($rows) 
	{	
		echo json_encode($rows); 
	} else
		echo 'false';

}
//--------------ClearData----------------------------------------------------------------
if(isset($_POST['clearData']))
{
	unset($_SESSION['currentplayer']);
	$queryGrid = "UPDATE `grid` SET `gridIndex`='0,0,skyblue+0,1,skyblue+0,2,skyblue+0,3,skyblue+0,4,skyblue+0,5,skyblue+0,6,skyblue+0,7,skyblue+0,8,skyblue+0,9,skyblue+1,0,skyblue+1,1,skyblue+1,2,skyblue+1,3,skyblue+1,4,skyblue+1,5,skyblue+1,6,skyblue+1,7,skyblue+1,8,skyblue+1,9,skyblue+2,0,skyblue+2,1,skyblue+2,2,skyblue+2,3,skyblue+2,4,skyblue+2,5,skyblue+2,6,skyblue+2,7,skyblue+2,8,skyblue+2,9,skyblue+3,0,skyblue+3,1,skyblue+3,2,skyblue+3,3,skyblue+3,4,skyblue+3,5,skyblue+3,6,skyblue+3,7,skyblue+3,8,skyblue+3,9,skyblue+4,0,skyblue+4,1,skyblue+4,2,skyblue+4,3,skyblue+4,4,skyblue+4,5,skyblue+4,6,skyblue+4,7,skyblue+4,8,skyblue+4,9,skyblue+5,0,skyblue+5,1,skyblue+5,2,skyblue+5,3,skyblue+5,4,skyblue+5,5,skyblue+5,6,skyblue+5,7,skyblue+5,8,skyblue+5,9,skyblue+6,0,skyblue+6,1,skyblue+6,2,skyblue+6,3,skyblue+6,4,skyblue+6,5,skyblue+6,6,skyblue+6,7,skyblue+6,8,skyblue+6,9,skyblue+7,0,skyblue+7,1,skyblue+7,2,skyblue+7,3,skyblue+7,4,skyblue+7,5,skyblue+7,6,skyblue+7,7,skyblue+7,8,skyblue+7,9,skyblue+8,0,skyblue+8,1,skyblue+8,2,skyblue+8,3,skyblue+8,4,skyblue+8,5,skyblue+8,6,skyblue+8,7,skyblue+8,8,skyblue+8,9,skyblue+9,0,skyblue+9,1,skyblue+9,2,skyblue+9,3,skyblue+9,4,skyblue+9,5,skyblue+9,6,skyblue+9,7,skyblue+9,8,skyblue+9,9,skyblue'";
	mysqli_query($connection,$queryGrid);
	$queryMessage = "UPDATE `messages` SET `playerMessages`=''";
	mysqli_query($connection,$queryMessage);
	echo "ok";

}

?>