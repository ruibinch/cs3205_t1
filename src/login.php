<?php

    $loginSystem = "Healthcare System";
    if (isset($_GET["to"])) {
        if ($_GET["to"] == "console") {
            $loginSystem = "Management Console";
        }
    }

?>

<html>
<meta charset="utf-8">

    <head>
        <title>Login</title>
        <link href="css/main.css" rel="stylesheet">
        <script	src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="ext/bcrypt.js"></script>
        <script src="ext/sha256.js"></script>
    </head>

    <body id="body" class="login">
        <h1>
            <font size="30px" face="Lucida Grande" color="white">CS3205 Team 1</font><br>
            <font size="5px" face="Lucida Grande" color="white" id="loginsystem"></font>
        </h1>
        <div class="login-container">
            <div class="login-tab">
                <button class="tablinks user-tab active" onclick="openTab(event, 'User'), toggleLoginSystem('User')">User</button>
                <button class="tablinks mgmt-tab" onclick="openTab(event, 'Management'), toggleLoginSystem('Management')">Management</button>

                <div id="User" class="login-tabcontent" style="display: block;">
                    <div class="login-form" id="form-user" name="form-user" method="post">
                        <input id="hc-username" name="hc-username" type="text" class="login-input" placeholder="Username" autofocus>
                        <input id="hc-password" name="hc-password" type="password" class="login-input" placeholder="Password">
                        <button name="user_type" value="patient" id="btn-login-patient" class="login-btn login-btn-patient">Login as Patient</button>
                        <button name="user_type" value="therapist" id="btn-login-therapist" class="login-btn login-btn-therapist">Login as Therapist</button>
                    </div>
                </div>

                <div id="Management" class="login-tabcontent">
                    <form class="login-form" name="form-mgmt" method="post" action="management/process.php">
                        <input id="mgmt-username" name="mgmt-username" type="text" class="login-input" placeholder="Username" autofocus>
                        <input id="mgmt-password" name="mgmt-password" type="password" class="login-input" placeholder="Password">
                        <button id="btn-login" name="login" class="login-btn login-btn-mgmt">Login</button>
                    </form>
                </div>

                <?php
                    if (isset($_GET['err']) && ($_GET['err'] === "1" || $_GET['err'] === "2")) {
                        echo '<br/><br/><h3 style="color: red;">&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Invalid username or password.</h3>' . "\n";
                    }
                ?>	
            </div>
        </div>

        <script>

            var userType;

            $(document).ready(function() {

                $('#form-user').on('click', "button", function() {
                    userType = $(this).val();
                    $.ajax({
                        type: "POST",
                        url: "util/ajax-process.php",
                        data: { "input_username": $('#hc-username').val() }
                    }).done(function(response) {
                        if (response == 0) {
                            window.location = "login.php?err=1"; // no such user exists
                        } else {
                            obj = JSON.parse(response);
                            computeResponse(obj);
                        }
                    });
                });

            });

            // Compute the response for the challenge/response authentication process
            function computeResponse(obj) {
                console.log("challenge = " + obj.challenge);
                var bcrypt = dcodeIO.bcrypt;

                var bcrypt_pw = bcrypt.hashSync($('#hc-password').val(), obj.salt); // bcrypt(pw, salt);
                var sha_bcrypt_pw = sha256(bcrypt_pw); // H(bcrypt(pw,salt))
                var sha_sha_bcrypt_pw_with_challenge = sha256(sha_bcrypt_pw + obj.challenge); // H(H(bcrypt(pw,salt)) || challenge)

                // convert to binary and XOR the binary equivalents
                var binary1 = hexToBinary(sha_sha_bcrypt_pw_with_challenge);
                var binary2 = textToBinary(bcrypt_pw);
                var response = xor(binary1, binary2);
                
                //console.log("bcrypt(pw,salt) = " + bcrypt_pw);
                //console.log("H(bcrypt(pw,salt)) = " + sha_bcrypt_pw);
                //console.log("H(H(bcrypt(pw,salt)) || challenge) = " + sha_sha_bcrypt_pw_with_challenge);
                
                sendResponse(response, obj.challenge);
            }

            function sendResponse(response, challenge) {
                $.ajax({
                    type: "POST",
                    url: "util/ajax-process.php",
                    data: { "response": response }
                }).done(function(response) {
                    if (response == 0) {
                        window.location = "login.php?err=1";
                    } else {
                        var url = "util/login-validation.php";
                        var username = $('#hc-username').val();
                        var form = $('<form action="' + url + '" method="post">' + 
                                    '<input type="text" name="username" value="' + username + '" />' + 
                                    '<input type="text" name="user_type" value="' + userType + '" />' +
                                    '</form>');
                        $('#body').append(form);
                        form.submit();
                    }
                });
            }

            window.onload = function() {
                document.getElementById("loginsystem").innerHTML = "<?php echo $loginSystem ?>";
                <?php
                    //test code to default to management tab if it is selected. feel free to remove if desired.
                    //small bug: dk how to change to active class for management..... javascript rusty liao. zz
                    //it is broken on firefox too... 
                    if ($loginSystem === "Management Console") {
                        echo 'openTab(event, \'Management\');' . "\n";
                        echo 'toggleLoginSystem(\'Management\');' . "\n";
                    }
                ?>
            }

            function openTab(evt, tabName) {
                var i, tabcontent, tablinks;
                tabcontent = document.getElementsByClassName("login-tabcontent");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }
                tablinks = document.getElementsByClassName("tablinks");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }
                document.getElementById(tabName).style.display = "block";
                evt.currentTarget.className += " active";
            }

            function toggleLoginSystem(tabName) {
                if (tabName == "User") {
                    document.getElementById("loginsystem").innerHTML = "Healthcare System";
                    openTab(event, 'User');
                } else if (tabName == "Management") {
                    document.getElementById("loginsystem").innerHTML = "Management Console";
                    openTab(event, 'Management');
                }
            }

            // ===============================================================================
            //          HELPER FUNCTIONS FOR CHALLENGE-RESPONSE AUTHENTICATION
            // ===============================================================================
            
            function hexToBinary(input) {
                var binary = "";
                for (var i = 0; i < input.length; i++) {
                    var x = parseInt(input[i], 16).toString(2);
                    while (x.length < 4) {
                        x = '0' + x;
                    }
                    binary += x;
                }
                return binary;
            }

            function textToBinary(input) {
                var binary = "";
                for (var i = 0; i < input.length; i++) {
                    var x = input[i].charCodeAt(0).toString(2);
                    while (x.length < 8) {
                        x = '0' + x;
                    }
                    binary += x;
                }
                return binary;
            }
            
            function xor(a, b) {
                var result = "";

                // pad to equal length
                //console.log("Before padding: " + "a.length = " + a.length + ", b.length = " + b.length);
                if (a.length > b.length) {
                    while (a.length > b.length) {
                        b = '0' + b;
                    }
                } else if (b.length > a.length) {
                    while (b.length > a.length) {
                        a = '0' + a;
                    }
                }
                //console.log("After padding: " + "a.length = " + a.length + ", b.length = " + b.length);

                // perform XOR
                for (var i = 0; i < a.length; i++) {
                    result += a[i] ^ b[i];
                }
                
                return result;
            }


        </script>

    </body>
</html>