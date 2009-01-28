<?php /* Smarty version 2.6.9, created on 2006-09-26 21:59:27
         compiled from Forum/addMessage.tpl */ ?>
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
<body style="width:95%;background-color:#FF5F05">
<div style="width:100%;border:1px solid #ccc" align="center">
  <div style="width:800px;text-align:left;border:1px solid #ccc;background-color:#fff;margin-top:50px;padding:15px" align="left" class="text">
    <h2 style="display:inline">Test Page</h2><br /><br />
    <b>Presentation Layer</b> communicating with <b>Logic Layer</b>: <?php if ($this->_tpl_vars['content'] == true): ?>Successful!<?php else: ?>Failed!<?php endif; ?><br/><br/>
    <a href="<?php echo $this->_tpl_vars['domain']; ?>
">Back</a>
</div>  
</div>
</body>
</html>