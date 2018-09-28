<div style="max-height:87%;">
    <div >
        <div style="height: 100%; width: 100%;">
            <div class="row-fluid">
                <div class="span6" style="background:rgba(255, 255, 255, .2); padding:30px; padding-top: 100;  height: 100%">

                   <h1 align="center" style="font-size:50px; font-family: Futura; color:#f5f5f5">Gather</h1>
                    <h1 align="center" style="font-size:50px; font-family: Futura;">Xpriences</h1>
                    <h1 align="center" style="font-size:50px; font-family: Futura; color:#f5f5f5">Intelligently.</h1>
                </div>
                <div class="span5"  style="background:rgba(255, 255, 255, .3);height: 100%;overflow:auto">
                    <div style="padding: 10%; ">
                        <form id="login_user_form" action="<?php echo Bones::get_instance()->make_route('/login') ?>" method="post">
                            <div class="form-group" style="">
                                <div>
                                    <input style="font-family:Futura; font-size:large; border-radius: 5px;margin:10px; width:100%;" id="login_user_name_box" name="login_user_name_box"  type="input"  placeholder="username@company.com">
                                </div>
                                <div>
                                    <input style=" font-family:Futura; font-size:large; border-radius: 5px; margin:10px; height:50px; padding: 2px; width:100%" id="login_user_password_box" name="login_user_password_box"  type="password"  placeholder="Password">
                                </div>
                                <div>
                                    <button style="font-family:Futura; float:right; margin:0" class="btn-large btn-primary"><i class="icon-user"></i> Login</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div style=" margin-left:10%;  padding: 10%;  margin-right: 10%;">
                        <hr style="border-color: #f5f5f5; width:40%; float: left">
                        <p style="float: left;width:20%; color:#f5f5f5;  text-align:center">OR</p>
                        <hr style="border-color: #f5f5f5; width:40%;float: left">
                    </div>
                    <div style=" margin-left:10%;  margin-right: 10%;">
                        <p style="background-color: lightgray; padding:10px; color:#000000; font-size: 20px; text-align:center">New researchers and study coordinators can register an account below</p>
                    </div>

                    <div style="padding:10%;">
                        <form id="create_user_form"  action="<?php echo Bones::get_instance()->make_route('/register') ?>" method="post">

                                <div>
                                    <input style="font-family:Futura; border-radius: 5px;margin:10px; font-size:large; width:100%;" name="create_user_first_box"  type="input"  placeholder="First Name">
                                </div>
                                <div>
                                    <input style=" font-family:Futura; border-radius: 5px;margin:10px; font-size:large; width:100%;" name="create_user_last_box"  type="input"  placeholder="Last Name">
                                </div>
                                <div>
                                    <input style="font-family:Futura; border-radius: 5px;margin:10px; font-size:large; width:100%;" name="create_user_name_box"  type="input"  placeholder="Email">
                                </div>
                                <div>
                                    <input style=" font-family:Futura; border-radius: 5px; margin:10px; font-size:large; height:50px; padding: 2px; width:100%" name="create_user_secret_box"  type="password"  placeholder="Password">
                                </div>
                                <div>
                                    <button style="font-family:Futura; float:right; margin:0" class="btn-large btn-primary"><i class="icon-plus-sign"></i> Register</button>
                                </div>
                        </form>
                    </div>
                </div>
                <div class="span1"  style="background:rgba(255, 255, 255, .4);height: 100%">
                </div>
            </div>
        </div>
    </div>
</div>