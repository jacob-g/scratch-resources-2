<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
<title><$page_title/></title>
<link rel="stylesheet" type="text/css" href="<?php echo $basepath; ?>/styles/default.css" />
<link rel="icon" href="/favicon.ico" />
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />

</head>
<body>
<div id="pagewrapper">
       
      
      <div id="topnav" >
        <div class="container">
          <a href="<?php echo $basepath; ?>/" class="logo"><span class="scratch"></span></a>
          <ul class="site-nav">
            <li><a id="project-create" href="<?php echo $basepath; ?>/upload">Create</a></li><li><a href="<?php echo $basepath; ?>/view/latest">Explore</a></li><li><a href="<?php echo $basepath; ?>/about">About</a></li><li class="last"><a href="http://scratch.mit.edu/help/">Help</a></li>
          </ul>
            <form action="/search/projects/" method="get" class="search">
            <input type="submit" class="glass" value="" />
        
        		<input type="text" placeholder="Search" name="q" />
               
              <input type="hidden" name="date" value="anytime" />
          	<input type="hidden" name="sort_by" value="datetime_shared" />
          </form>
            
            <ul class="account-nav">
		  	<?php if ($ms_user['valid']) {
				?>
				<li class="loggedinbutton">
					<a href="<?php echo $basepath; ?>/users/<?php echo $ms_user['username']; ?>"><?php echo $ms_user['username']; ?></a>
				</li>
				<li class="loggedinbutton">
					<a href="<?php echo $basepath; ?>/settings">Settings</a>
				</li>
				<li class="loggedinbutton">
					<a href="<?php echo $basepath; ?>/login?out">Log out</a>
				</li>
				<script type="text/javascript">
				/*document.getElementById('userbutton').onclick = function() {
					if (document.getElementById('user-nav').style.display == 'block') {
						document.getElementById('user-nav').style.display = 'none';
					} else {
						document.getElementById('user-nav').style.display = 'block';
					}
				};*/
				</script>
				<?php
			} else {
				?>
               <li class="join-scratch" data-control="registration"><span class=""><span><a href="<?php echo $basepath; ?>/signup">Sign up</a></span></span></li><li id="login-dropdown" class="sign-in dropdown"><span data-toggle="dropdown" class="dropdown-toggle"><span><a href="<?php echo $basepath; ?>/login">Log in</a></span></span></li>
			<?php 
			} ?>
            </ul>
          
        </div>
      </div> 
        
    
        
      <div class="container" id="content">