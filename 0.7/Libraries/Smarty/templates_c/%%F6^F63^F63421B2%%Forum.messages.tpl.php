<?php /* Smarty version 2.6.9, created on 2006-08-09 22:03:00
         compiled from Forum.messages.tpl */ ?>
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
  <div style="width:800px;text-align:left;border:1px solid #ccc;background-color:#fff;margin-top:50px;padding:15px" align="left" class="text">
    <h2 style="display:inline">Your installation was successful!</h2><br /><br />
    You now have a successful installation of <b>PHP-Controller</b>. To start building your web application, simply edit <b>controller.xml</b>, drop your PHP classes into <b>Modules/</b> and your templates into <b>Presentation/</b>.<br /><br />

		Also, make sure that <b>Libraries/Smarty/template_c</b> is writeable.<br /><br />
    
    For more help, see the <a href="http://users.visudo.com/eddie/PHP-Controller/#installation" title="PHP-Controller Documentation">online documentation</a>.<br /><br />
    
    <a href="<?php echo $this->_tpl_vars['domain']; ?>
forum/addMessage">Test Page.</a><br />
    <a href="<?php echo $this->_tpl_vars['domain']; ?>
administrator">Test Restrictioned Area.</a><br /><br />
    
  </div>  
</div>
</body>
</html>