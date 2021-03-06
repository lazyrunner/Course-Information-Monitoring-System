
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
	session_start();
	//the below code block is required as it controls which user can access the pages,please don't remove it
	if(isset($_SESSION['unames'])) //every page checks if logged in and ,and if not then go to login page
    {   
     if($_SESSION['isAdmin']!=0) //if not faculty go to index page
     {
      header('Location: index.php'); 
     }  
    }else header('Location: index.php'); 
		

	include 'connection.php'; 
	//fetching main information
	if ($conn->connect_error) { //Check connection
		die("Connection failed: " . $conn->connect_error);
	}
	$sql = "SELECT id,name FROM faculty where uname='".$_SESSION["unames"]."' ";
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	$faculty_id=$row["id"];
	$faculty_name=$row["name"];
	$faculty_code=$_SESSION["unames"];
	$sql = "SELECT * FROM subject where faculty_id='".$faculty_id."' ";
	$result = $conn->query($sql);
	$no_of_subjects=0;
	while($row = $result->fetch_assoc())
	{
		$subject_code[$no_of_subjects]=$row["subject_code"];
		$subject_id[$no_of_subjects]=$row["id"];
		$subject_name[$no_of_subjects]=$row["subject_name"];
		$subject_section[$no_of_subjects]=$row["section"];
		$no_of_subjects++;
	}	
		
	
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Course Information Monitoring System </title>
<link rel="stylesheet" type="text/css" href="style.css" />
<link href='http://fonts.googleapis.com/css?family=Belgrano' rel='stylesheet' type='text/css'>
<!-- jQuery file -->
<script src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/multiple.js"></script>
<script src="js/jquery.tabify.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
var $ = jQuery.noConflict();
$(function() {
$('#tabsmenu').tabify();
$(".toggle_container").hide(); 
$(".trigger").click(function(){
	$(this).toggleClass("active").next().slideToggle("slow");
	return false;
});


});

$(document).ready(function(){
    var next = 1;
    $(".add-more").click(function(e){
        e.preventDefault();
        var addto = "#field" + next;
		var addto2 = "#hfield" + next;
        var addRemove = "#field" + (next);
        next = next + 1;
        var newIn = '<input autocomplete="off" class="form_input" id="field' + next + '" name="' + next + '" type="text">';
		var newIn2 = '<input id="hfield'+next+'" name="h'+nect+'" type="text"/>';
        var newInput = $(newIn);
		var newInput2 = $(newIn2);
        var removeBtn = ' <button id="remove' + (next - 1) + '" class="remove-me" >-</button></div><div id="field">';
        var removeButton = $(removeBtn);
        $(addto).after(newInput);
		$(addto2).after(newInput2);
        $(addRemove).after(removeButton);
        $("#field" + next).attr('data-source',$(addto).attr('data-source'));
		$("#hfield" + next).attr('data-source',$(addto2).attr('data-source'));
        $("#count").val(next);  
        
            $('.remove-me').click(function(e){
                e.preventDefault();
                var fieldNum = this.id.charAt(this.id.length-1);
                var fieldID = "#field" + fieldNum;
				var fieldID2 = "#hfield" + fieldNum;
                $(this).remove();
                $(fieldID).remove();
				$(fieldID2).remove();
            });
		$('#counter').val( function(i, oldval) {
        return ++oldval;
		});	
    });
    

    
});


</script>
</head>
<body>
<div id="panelwrap">
  	
	<div class="header">
    <div class="title"><a href="#">Course Information Monitoring System</a></div>
    
    <div class="header_right">Welcome <?php echo $faculty_name."(".$faculty_code.")"; ?><a href="logout.php" class="logout">Logout</a> </div>
    
    <div class="menu">
    <ul>
    <li><a href="faculty.php" class="selected">Main page</a></li>
    <li><a href="faculty_add_content.php">Add Subject Content</a></li>
    <li><a href="faculty_notification.php">Notification</a></li>

    </ul>
    </div>
    
    </div>
             
    <div class="center_content">  
 
    <div id="right_wrap">
    <div id="right_content">             
    <h2>Add Subject</h2> 
                    
                    

   
	<?php
	
	
	$to_add_counter='<input type="hidden"  name="chap_count"  value="1" />';
	$chap_count=1;
	
	if(isset($_POST['chap_count']))
	{
		$chap_count=$_POST['chap_count'];
		$chap_count++;
		$to_add_counter='<input type="hidden" name="chap_count"  value="'.$chap_count.'" />';
		
		// -----------------code to add things to the database;
		$arr_of_chap = $_POST['units_to_add'];
		$arr_of_hrs = $_POST['est_hrs_of_each'];
		$total_est_hrs=0;
		$random=0;
		$sql="";
		foreach($arr_of_chap as $x){
			//echo $x." ".$arr_of_hrs[$random++]." ".($chap_count-1)." ".$_POST["subject_content"]."==";
			$total_est_hrs=$total_est_hrs+$arr_of_hrs[$random++];
			$t=$random-1;
			$sql.="INSERT INTO `cms`.`course_info` VALUES (NULL, '".($chap_count-1)."', '".$random."', '".$x."', '".$arr_of_hrs[$t]."', '0', '".$_POST["subject_content"]."');";
		}
		$sql.="INSERT INTO `cms`.`course_info` VALUES (NULL, '".($chap_count-1)."', '0', '".$_POST["chapter_name"]."', '".$total_est_hrs."', '1', '".$_POST["subject_content"]."'); ";
		//echo $sql;             to check query
		if (!$conn->multi_query($sql)) {
			echo "Multi query failed: (" . $conn->errno . ") " . $conn->error;
		}
		if($chap_count > $_POST["unit_nos"]) //if all Chapters added
		 {
		  header('Location: faculty_add_content.php?chapters_added=1'); 
		 }
	}	
	else{
		$sql = "DELETE  a, b 
		FROM course_info a
        INNER JOIN progress b
            ON b.subject_code=a.sub_code
				WHERE  a.sub_code='".$_POST["subject_content"]."'";
		$result = $conn->query($sql);
	}
	
	?>
    
    <div id="tab1" class="tabcontent">
    
        <div class="form">
            <form action="" method="post">
					<input type="hidden" name="subject_content"  value="<?php echo $_POST["subject_content"]; ?>" />
					<input type="hidden" name="unit_nos" value="<?php echo $_POST["unit_nos"]; ?>" />
					<?php echo $to_add_counter; ?>
					<div class="form_row">
					<label>Chapter <?php echo $chap_count; ?> Title:</label>
					<input type="text" class="form_input" name="chapter_name" />
					</div>
					
					<div class="form_row">
									<label>Unit Titles:</label>
									</div>
									<div class="form_row">
									<p> 
									<input type="button" value="Add Unit" onClick="addRow('dataTable')" /> 
									<input type="button" value="Remove Selected Unit" onClick="deleteRow('dataTable')"  /> 
								</p>
							   <table id="dataTable" class="form" border="0" width="600">
								  <tr><th></th><th>Unit</th><th>est hrs</th></tr>
								  <tbody>
								  
									<tr>
									  <p>
										<td><input type="checkbox"  name="chk[]"  /></td>
										<td>
											
											<input type="text"  name="units_to_add[]" size="70">
										 </td>
										 <td >
											
											<input type="text"  class="small"  name="est_hrs_of_each[]" size="3">
										 </td>
										 
											</p>
									</tr>
									</tbody>
								</table>
					</div>
					<div class="form_row">
					<input type="submit" class="form_submit" value="Next Chapter" />
					</div> 
					<div class="clear"></div>
					
			</form>
        </div>
    </div>
    
	
     
    

    
        <div class="toogle_wrap">
            <div class="trigger"><a href="#">Toggle with text</a></div>

            <div class="toggle_container">
			<p>
        Lorem ipsum <a href="#">dolor sit amet</a>, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.Lorem ipsum <a href="#">dolor sit amet</a>, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
			</p>
            </div>
        </div>
      
     </div>
     </div><!-- end of right content-->
                     
                    
    <div class="sidebar" id="sidebar">
    <h2>Subjects Taking</h2>
    
        <ul>
		<?php
			for($i=0;$i<$no_of_subjects;$i++)
			{
				echo "<li><a href='faculty_subject.php?subject_code=".$subject_code[$i]."&section_sel=".$subject_section[$i]."&subject_id=".$subject_id[$i]."' >".$subject_name[$i]." - ".$subject_section[$i]." -(".$subject_code[$i].") </a></li>";
			}
		?>
        </ul>
        
   
   
    <h2>Information</h2> 
    <div class="sidebar_section_text">
		Press the "+" button to add more units in this chapter, in case you want to remove extra units select them in the check box and select the remove button.
    </div>         
    
    </div>             
    
    
    <div class="clear"></div>
    </div> <!--end of center_content-->
    


</div>

    	
</body>
</html>

