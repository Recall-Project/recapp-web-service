<html>
<head>

    <link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" media="screen" href="http://tarruda.github.com/bootstrap-datetimepicker/assets/css/bootstrap-datetimepicker.min.css">

    <link href="http://www.stylebootstrap.info/otherStyles/JfNcQGnxfKKnGhq1WAsEu.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo Bones::get_instance()->make_route('/css/master.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo Bones::get_instance()->make_route('/css/slider.css') ?>" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">

    <script src="http://code.jquery.com/jquery.min.js"></script>
    <script src="<?php echo Bones::get_instance()->make_route('/js/bootstrap.js') ?>"></script>
    <script src="<?php echo Bones::get_instance()->make_route('/js/jquery.cookie.js') ?>"></script>
    <script type="text/javascript" src="http://tarruda.github.io/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.min.js"></script>
    <script src="<?php echo Bones::get_instance()->make_route('/js/bootstrap-slider.js')?>"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>


    <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/handlebars.js/1.0.rc.1/handlebars.min.js"></script>
    <script src="<?php echo Bones::get_instance()->make_route('/js/templates.js') ?>"></script>
			
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif] -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBsUGnK6YXsKq-j84A_obdxBpD8hIEfmW0"></script>

	
</head>
	<body style="height:100%;  width:100%; background: url('<?php echo Bones::get_instance()->make_route('public/img/spiral.jpg') ?>') repeat; background-size:100% 100%;">


    <div class="navbar" style="margin-bottom:0px; position: relative; width:100%">
        <div class="navbar-inner">
            <a class="brand" href="#">Xpr <span style="font-size:10px">(beta)</span></a>
            <ul class="nav">
                <?php if (User::isAuthenticated()) { ?>
                    <li><a class="navbaritem" href="<?php echo Bones::get_instance()->make_route('/projects')?>">Studies</a></li>
                    <li><a class="navbaritem" href="<?php echo Bones::get_instance()->make_route('/logout')?>">logout</a></li>
                <?php } else { ?>

                <?php } ?>
            </ul>
        </div>
    </div>
    <?php echo $this->display_alert('error');?>
    <?php echo $this->display_alert('success');?>

    <div style="height:auto; padding-top: 60px">
        <?php include($this->content);?>
    </div>

    <div style="height:13%;  width:100%; background:rgba(225, 225, 225, .9);">
        <div class="row-fluid">
            <div class="span4" style="background:rgba(255, 255, 255, .2); padding:20px;  height: 100%">
                <p align="left" style="font-size:12px; font-family: Futura; color:#f5f5f5">&copy 2018 RECALL Project</p>
            </div>
            <div class="span6" style="background:rgba(245, 245, 245, .5); padding:10px;  height: 100%">
                <img height="20%" width="20%" src="<?php echo Bones::get_instance()->make_route('public/img/reflect.png') ?>">
            </div>
            <div class="span2"  style="background:rgba(255, 255, 255, .3);padding:10px; height: 100%">
                <div align="center">
                </div>
            </div>
        </div>
    </div>

    </body>
</html>
