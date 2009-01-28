<?php /* Smarty version 2.6.9, created on 2006-08-09 22:05:48
         compiled from Authentication.tpl */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>PHP Controller 0.7.0</title>
	<?php echo '
  <style type="text/css">
	.text{font-size:0.8em;font-family:Lucida Grande,Verdana,sans-serif;color:#0C2D4D}
	body{margin:0px}
	a{color:#FF5F05;text-decoration:none}
	a:hover{color:#FF5F05;text-decoration:underline}
	</style>
	'; ?>


  
</head>
<body style="width:95%;background-color:#ddd">
<div style="width:100%;border:1px solid #ccc" align="center">
  <div style="width:400px;border:1px solid #ccc;background-color:#fff;margin-top:50px;padding:15px" align="center" class="text">
    default user name and password is "admin" 
    <table width="300" border="0" cellspacing="2" cellpadding="5" style="border:1px solid #DDDDDD;background-color:#EEEEEE">
    <form action="<?php echo $this->_tpl_vars['domain']; ?>
authentication/login" method=post>
      <tr style="background-color:#EEEEEE">
       <td>Username</td>
       <td><input type="text" name="username"></td>
      </tr>
      <tr>
       <td>Password</td>
       <td><input type="password" name="password"></td>
      </tr>
      <tr>
       <td>&nbsp;</td>
       <td><input type="submit" value="Login"></td>
      </tr>
    </form>
  </table>
	<br/>
  <a href="<?php echo $this->_tpl_vars['domain']; ?>
">Back</a>
  </div>  
</div>
</body>
</html>