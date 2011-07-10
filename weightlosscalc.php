<?php
/*
Plugin Name: Weight Loss Calculator
Plugin URI: http://calendarscripts.info/weight-loss-calculator-wordpress-plugin.html
Description: This plugin displays functional weight loss/gain planning calculator. It helps calculate the calories intake to reach a sertaing goal.
Author: Bobby Handzhiev
Version: 1.1
Author URI: http://calendarscripts.info
*/ 

/*  Copyright 2008  Bobby Handzhiev (email : info@calendarscripts.info)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


function weightloss_add_page()
{
	add_submenu_page('plugins.php', 'Weight Loss Calculator', 'Weight Loss Calculator', 8, __FILE__, 'weightloss_options');
}

// ovpredct_options() displays the page content for the Ovpredct Options submenu
function weightloss_options() 
{
    // Read in existing option value from database
    $wlc_table = stripslashes( get_option( 'wlc_table' ) );
    
    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ 'wlc_update' ] == 'Y' ) 
    {
        // Read their posted value
        $wlc_table = $_POST[ 'wlc_table' ];
        

        // Save the posted value in the database
        update_option( 'wlc_table', $wlc_table );
        
        // Put an options updated message on the screen
		?>
		<div class="updated"><p><strong><?php _e('Options saved.', 'wlc_domain' ); ?></strong></p></div>
		<?php		
	 }
		
		 // Now display the options editing screen
		    echo '<div class="wrap">';		
		    // header
		    echo "<h2>" . __( 'Weight Loss Calculator', 'wlc_domain' ) . "</h2>";		
		    // options form		    
		    ?>
		
		<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="wlc_update" value="Y">
		
		<p><?php _e("CSS class definition for the weight loss calculator  &lt;div&gt;:", 'wlc_domain' ); ?> 
		<textarea name="wlc_table" rows='5' cols='70'><?php echo stripslashes ($wlc_table); ?></textarea>
		</p><hr />
		
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Options', 'wlc_domain' ) ?>" />
		</p>
		
		</form>
		</div>
		<?php
}

// This just echoes the text
function weightlosscalc($content) 
{	
	if(!strstr($content,"{{weight-loss-calculator}}")) return $content;
	
	//construct the calculator page	
	$css=get_option('wlc_table');
	$wl_calc="<style type=\"text/css\">	
	$css
	</style>\n\n";
	
	if(empty($css))
	{
		$inline_style="style='margin:auto;padding:5px;width:450px;text-align:left;'";
	}
	
	if(!empty($_POST['calculator_ok']))
	{
		// save in session to be used when the link "calculate again" is clicked
		foreach($_POST as $key=>$var) $_SESSION["calc_".$key]=$var;
	
		// calculate height in incles 
		$height_in=$_POST["height_ft"]*12+$_POST["height_in"];	
		
		// calculate BMR
		if($_POST["gender"]=='male')
		{
			$BMR=66 + (6.3 * $_POST["weight_lb"]) + (12.9 * $height_in) - (6.8 * $_POST["age"]);			
		}
		else
		{
			$BMR=655 + (4.3 * $_POST["weight_lb"]) + (4.7 * $height_in) - (4.7 * $_POST["age"]);
		}
		
		// calculate activity
		$activity=$BMR*$_POST["activity"];
		
		// calories to maintain current weight
		$calories=round($BMR+$activity);
		
		// how many pounds do you want to lose per day?
		$pounds_daily=round($_POST["lose_lb"]/$_POST["days"],2);
		
		if($pounds_daily>0.3) $high_risk_weight=true;
		else $high_risk_weight=false;
		
		// how much less calories does that mean?
		$calories_less=round($pounds_daily*3500,0);
		
		// how much calories daily does that mean
		$calories_daily=$calories-$calories_less;
		
		// is it too risky?
		if($calories_daily<1200) $high_risk_calories=true;
		else $high_risk_calories=false;
			
		//the result is here
		$wl_calc.='<div class="wlc_table" '.$inline_style.'>';		
		
		if($high_risk_weight)
		{
			$wl_calc.='<div style="color:red;font-weight:bold;border:1pt solid black;">Warning: your goal requires you to lose '.number_format($pounds_daily*7).' pounds per week. This implies a high risk for your health and is not recommended!</div>';		
		}
		if($high_risk_calories)
		{
			$wl_calc.='<div style="color:red;font-weight:bold;border:1pt solid black;">Warning: your goal requires you to lose '.$calories_less.' calories per day, which means you are supposed to intake only '.$calories_daily.' calories daily. This implies a high risk for your health and is not recommended!</div>';		
		}
		
		$wl_calc.='<p>Your goal is to lose <b>'.$_POST["lose_lb"].' lb</b> / <b>'.$_POST["lose_kg"].' kg</b> for <b>'.$_POST["days"].' days</b>. </p>
		<p>To maintain your current weight, your safe daily calories intake is around <b>'.$calories.' calories</b></p>
		<p>To reach your goal, you will need to reduce your daily calories intake with <b>'.$calories_less.' calories</b>, which means to get <b>'.$calories_daily.' calories daily</b>.</p>
		<p align="center"><a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">Calculate again</a></p>
	    </div>';
		
	}
	else
	{
		$wl_calc.=<<<WL_CALC
		<form method="post" onsubmit="return validateCalculator(this);">
		<table class="wlc_table" $inline_style ="text-align:left;">		
		<tr><td width="150"><label for="yourAge">Your age:</label></td><td width="300"><input type="text" name="age" id="yourAge" size="6" value="$_SESSION[calc_age]"></td></tr>
		<tr><td><label for="yourGender">Your gender:</label></td><td><select name="gender">
		<option value="male">Male</option>
		<option value="female">Female</option>
		</select></td></tr>
		<tr><td><label>Your height:</label></td><td> <input type="text" name="height_ft" size="4" onkeyup="calculateHeight(this);"> ft &amp; <input type="text" name="height_in" size="4" onkeyup="calculateHeight(this);"> in <b>OR</b> <input type="text" name="height_cm" size="5" onkeyup="calculateHeight(this);"> cm</td></tr>
		<tr><td><label>Your weight:</label></td><td><input type="text" name="weight_lb" size="4" onkeyup="calculateWeight(this);"> lbs <b>OR</b> <input type="text" name="weight_kg" size="4" onkeyup="calculateWeight(this);"> kg</td></tr>
		<tr><td><label for="dailyActivity">Daily activity level:</label></td>
		<td><select name="activity" id="dailyActivity">
		<option value="0.2">No sport/exercise</option>
		<option value="0.375">Light activity (sport 1-3 times per week)</option>
		<option value="0.55">Moderate activity (sport 3-5 times per week)</option>
		<option value="0.725">High activity (everyday exercise)</option>
		<option value="0.9">Extreme activity (professional athlete)</option>
		</select></td></tr>
		<tr><td><label>How much weight you wish to lose?</label></td><td><input type="text" name="lose_lb" size="4" onkeyup="calculateWeight(this);"> lbs <b>OR</b> 
		<input type="text" name="lose_kg" size="4" onkeyup="calculateWeight(this);"> kg</td></tr>
		<tr><td><label for="daysDiet">How much time do you have?</label></td><td> <input type="text" name="days" size="8" id="daysDiet"> days</td></tr>
		<tr><td style="text-align:center;clear:both;" colspan="2"><input type="submit" value="Calculate!"></td></tr></table>
		<input type="hidden" name="calculator_ok" value="1">
		</form>					
		
		<script language="javascript">
		function validateCalculator(frm)
		{
			var age=frm.age.value;
			if(isNaN(age) || age<6 || age > 125 || age=="")
			{
				alert("Please enter your age, numbers only.");
				frm.age.focus();
				return false;
			}
			
			var height_ft=frm.height_ft.value;
			if(isNaN(height_ft) || height_ft<0) height_ft="";
			var height_in=frm.height_in.value;
			if(isNaN(height_in) || height_in<0) height_in="";
			var height_cm=frm.height_cm.value;
			if(isNaN(height_cm) || height_cm<0) height_cm="";
			
			if(height_ft=="" && height_cm=="" && height_in=="")
			{
				alert("Please enter your height, numbers only");
				return false;
			}
			
			var weight_lb=frm.weight_lb.value;
			if(isNaN(weight_lb) || weight_lb<0) weight_lb="";
			var weight_kg=frm.weight_kg.value;
			if(isNaN(weight_kg) || weight_kg<0) weight_kg="";
			
			if(weight_kg=="" && weight_lb=="")
			{
				alert("Please enter your weight, numbers only.");		
				return false;
			}
			
			var lose_lb=frm.lose_lb.value;
			if(isNaN(lose_lb) || lose_lb<0) lose_lb="";
			var lose_kg=frm.lose_kg.value;
			if(isNaN(lose_kg) || lose_kg<0) lose_kg="";
			
			if(lose_kg=="" && lose_lb=="")
			{
				alert("Please enter how much weight you want to lose, numbers only.");		
				return false;
			}
			
			var days=frm.days.value;
			if(isNaN(days) || days<0 || days=="")
			{
				alert("Please enter how many days you have to reach the goal, numbers only.");
				frm.days.focus();
				return false;
			}
		}
		
		function calculateHeight(fld)
		{
			if(fld.name=="height_in" || fld.name=="height_ft")
			{
				// calculate height in inches
				if(isNaN(fld.form.height_in.value) || fld.form.height_in.value=="") inches=0;
				else inches=fld.form.height_in.value;
				
				if(isNaN(fld.form.height_ft.value) || fld.form.height_ft.value=="") feet=0;
				else feet=fld.form.height_ft.value;
				
				inches=parseInt(parseInt(feet*12) + parseInt(inches));
				
				h=Math.round(inches*2.54);
				
				fld.form.height_cm.value=h;
			}
			else
			{
				// turn cm into feets and inches
				if(isNaN(fld.value) || fld.value=="") cm=0;
				else cm=fld.value;
				
				totalInches=Math.round(cm/2.54);
				inches=totalInches%12;		
				feet=(totalInches-inches)/12;
				
				fld.form.height_ft.value=feet;
				fld.form.height_in.value=inches;
			}
		}
		
		function calculateWeight(fld)
		{
			if(fld.name=="weight_lb" || fld.name=="lose_lb")
			{
				// calculate in kg
				if(isNaN(fld.value) || fld.value=="") w=0;
				else w=fld.value;
				
				wKg=Math.round(w*0.453*10)/10;
				
				if(fld.name=="weight_lb") fld.form.weight_kg.value=wKg;
				else fld.form.lose_kg.value=wKg;
			}
			else
			{
				// calculate in lbs
				if(isNaN(fld.value) || fld.value=="") w=0;
				else w=fld.value;
				
				wP=Math.round(w*2.2);
				
				if(fld.name=='weight_kg') fld.form.weight_lb.value=wP;
				else fld.form.lose_lb.value=wP;
			}
		}
		</script>
WL_CALC;
	}
	
	$content=str_replace("{{weight-loss-calculator}}",$wl_calc,$content);
	return $content;
	
}

add_action('admin_menu','weightloss_add_page');
add_filter('the_content', 'weightlosscalc');

?>