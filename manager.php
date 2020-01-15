<?php
include "rc6.php";
include "connection.php";
?>

<html>
    <head>
        <title>Account Manager</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="static/bootstrap.min.css" rel="stylesheet">
        <link href="static/all.css" rel="stylesheet">
        <script src="static/jquery.min.js"></script>
        <script src="static/bootstrap.min.js"></script>
    </head>

    <body>

        <div class="container">
            <div class="row" style="padding-top:35;">
                <table class="table table-striped" id="mydata">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Email</th>
                            <th scope="col">Username</th>
                            <th scope="col">Encrypted Password</th>
                            <th scope="col">Key</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $sql = $pdo->prepare("SELECT * FROM user");
                            $sql->execute();
                            $data = $sql->fetchAll();

                            for($i=0; $i<count($data);$i++){
                                ?>
                                <tr>
                                    <td><?php echo $data[$i]["id"]?></td>
                                    <td><?php echo $data[$i]["email"]?></td>
                                    <td><?php echo $data[$i]["username"]?></td>
                                    <td><?php echo utf8_encode($data[$i]["password"])?></td>
                                    <td><?php echo $data[$i]["keygen"]?></td>
                                    <td><button data-toggle="modal" data-target="#modal-info" type="button" class="btn btn-default" onclick="info('<?php echo $data[$i]['id'];?>');">
                                    <span class="fa fa-search"></span></button></td>
                                </tr>
                            <?php 
                            }
                        ?>
                        
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modal-info" role="dialog">
            <div class="modal-dialog">
            <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header" style="padding:35px 50px;">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4><span class="fa fa-info"></span> RC-6 Info</h4>
                    </div>
                    <div class="modal-body" style="padding:40px 50px;">
                        <form role="form">
                            <div class="form-group">
                                <input type="hidden" id="id_" name="id_">

                                <label for="pwd-dec"><span class="fa fa-unlock"></span> Password</label>
                                <input type="text" class="form-control" id="pwd-dec" onkeyup="cpwd()">

                                <label for="key"><span class="fa fa-key"></span> Key</label>
                                <input type="text" class="form-control" id="key" onkeyup="cpwd()">

                                <label for="pwd-enc"><span class="fa fa-lock"></span> Password Encrypted</label>
                                <input type="text" class="form-control" id="pwd-enc" disabled>

                                <label for="pwd-enc2"><span class="fa fa-lock"></span> Password Encrypted (Hex)</label>
                                <input type="text" class="form-control" id="pwd-enc2" disabled>

                                <label for="sbox"><span class="fa fa-box"></span> S-Box</label>
                                <textarea class="form-control rounded-0" id="sbox" rows="7" disabled></textarea>

                            </div>
                            
                            <div class='alert alert-success alert-dismissible' id="notif">
                                <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
                                <strong>Berhasil!</strong> Data berhasil dirubah.
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="update()" >Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>                     

    </body>

    <script>
        function info(id){
            document.getElementById("notif").style.display = "none";
            $("#modal-info").show();

            $.ajax({
                type: "POST",
                url: 'getdata.php',
                data: { id : id },
                success: function(response)
                {
                    var data = JSON.parse(response);

                    $("#id_").val(id);
                    $("#pwd-dec").val(data["pwd-dec"]);
                    $("#key").val(data["key"]);
                    $("#pwd-enc").val(data["pwd-enc"]);
                    $("#pwd-enc2").val(data["pwd-enc2"]);
                    $("#sbox").val(data["sbox"]);
                }
            });
        }

        function cpwd() {
            var pass = $("#pwd-dec").val();
            var keyy = $("#key").val();

            $.ajax({
                type: "POST",
                url: 'editdata.php',
                data: { pass : pass, keyy : keyy },
                success: function(response)
                {
                    var data = JSON.parse(response);
                    $("#pwd-enc").val(data["pwd-enc"]);
                    $("#pwd-enc2").val(data["pwd-enc2"]);
                    $("#sbox").val(data["sbox"]);
                }
            });
        }

        function update(){
            var password = $("#pwd-dec").val();
            var keygen = $("#key").val();
            var id = $("#id_").val();
            console.log(id);

            $.ajax({
                type: "POST",
                url: 'updatedata.php',
                data: { password : password, keygen : keygen, id : id},
                success: function(response)
                {
                    document.getElementById("notif").style.display = "block";
                }
            });

        }
    </script>

</html>

<?php

?>