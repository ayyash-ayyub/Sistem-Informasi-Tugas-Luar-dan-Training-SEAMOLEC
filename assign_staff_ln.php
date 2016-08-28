<?php

    include "menu.php";



    $KegiatanDN  = new KegiatanDN();

    $Staff =  new Staff();

    $AssignStaffDN = new AssignStaffDN();

    $listStaffDN = new listStaffDN();



    $KegiatanLN  = new KegiatanLN();

    $AssignStaffLN = new AssignStaffLN();

    $listStaffLN = new listStaffLN();

    $mode=0;



    $i =1;

    $id = base64_decode($_GET['id']);

    $dataDN = $KegiatanLN->ViewbyId($id);                      



    $data = $dataDN->fetch_array();

    $tm = tgl_indo($data['tgl_mulai']);

    $ts = tgl_indo($data['tgl_selesai']);

    $timeStart = $data['tgl_mulai'];

    $timeEnd = $data['tgl_selesai'];



    if(isset($_POST['assignStaff'])){



        if(isset($_POST['nama'])){

            $nama = $_POST['nama'];

        }

        $status_kordinator = $_POST['status_kordinator'];

        $nama[] = $status_kordinator;



        $nama_kegiatan = base64_decode($_GET['id']);;

        $tgl_mulai = $data['tgl_mulai'];

        $tgl_selesai = $data['tgl_selesai'];



        $existingStaffName = array();

        $existingStaffId = array();

        $candidateStaffId = array();



        $deletekoordinator = $AssignStaffLN->deletesemua($id);

        if ($deletekoordinator) {

            $mode=2;



            //Proses validasi setiap staff yang di assign

            foreach ($nama as $id) {

                $list = $listStaffDN->checkStaff($id, $tgl_mulai, $tgl_selesai);

                $listData = $list->fetch_array();

                

                $exist = $listData['jumlah'];



                if($exist>=1){

                    $item = $Staff->ViewbyId($id)->fetch_array();

                    if(!in_array($item['nama'], $existingStaffName)){

                        $existingStaffName[] = $item['nama'];

                    }



                    if(!in_array($id, $existingStaffId)){

                        $existingStaffId[] = $id;

                    }     

                }else{

                    if(!in_array($id, $candidateStaffId)){

                        $candidateStaffId[] = $id;

                    } 

                }



                $list = $listStaffLN->checkStaff($id, $tgl_mulai, $tgl_selesai);

                $listData = $list->fetch_array();

                

                $exist = $listData['jumlah'];



                if($exist>=1){

                    $item = $Staff->ViewbyId($id)->fetch_array();

                    if(!in_array($item['nama'], $existingStaffName)){

                        $existingStaffName[] = $item['nama'];

                    }

                    if(!in_array($id, $existingStaffId)){

                        $existingStaffId[] = $id;

                    }       

                }

                else{

                    if(!in_array($id, $candidateStaffId)){

                        $candidateStaffId[] = $id;

                    } 

                }

            }



            foreach ($candidateStaffId as $candidateId){

                $linkLaporan = TRUE;

                $item = $KegiatanDN->ViewByStaff($candidateId);



                while($data = $item->fetch_array()) {

                    if(is_null($data['link_laporan']) && $data['tgl_selesai_kegiatan']<$timeStart){    

                        $linkLaporan = FALSE;

                    }

                }

                if(!$linkLaporan){

                    if(!in_array($candidateId, $existingStaffId)){

                        $existingStaffId[] = $candidateId;

                        $dataNama = $Staff->ViewbyId($candidateId)->fetch_array();

                        $existingStaffName[] = $dataNama['nama'];

                    }

                }



                $linkLaporan = TRUE;

                $item = $KegiatanLN->ViewByStaff($candidateId);

                while($data = $item->fetch_array()){

                    if(is_null($data['link_laporan']) && $data['tgl_selesai_kegiatan']<$timeStart){

                        $linkLaporan = FALSE;

                    }

                }

                if(!$linkLaporan){

                    if(!in_array($candidateId, $existingStaffId)){

                        $existingStaffId[] = $candidateId;

                        $dataNama = $Staff->ViewbyId($candidateId)->fetch_array();

                        $existingStaffName[] = $dataNama['nama'];

                        

                    }

                }

            }



            if(!empty($existingStaffName)){

                $mode=1;

            }



            foreach ($nama as $k) {

                if(!in_array($k, $existingStaffId)){

                    if($k==$status_kordinator){

                        $cek = $AssignStaffLN->CekKordinator($nama_kegiatan);

                            if($cek->num_rows==0){

                                $AssignStaffLN-> Create($k, 'Y',  $nama_kegiatan,  $tgl_mulai, $tgl_selesai);

                            }

                    }else{

                        $AssignStaffLN-> Create($k, 'N',  $nama_kegiatan,  $tgl_mulai, $tgl_selesai);

                    }        

                }

            }

        }

    }

    $id = base64_decode($_GET['id']);

    $dataDN = $KegiatanLN->ViewbyId($id);                      



    $data = $dataDN->fetch_array();

?>



        <!-- Page Content -->

            <div id="page-wrapper">

                <div class="row">

                    <div class="col-lg-12">

                        <h4 class="page-header">Assign Staff</h4>

                    </div>

                    <!-- /.col-lg-12 -->

                </div>

                <!-- /.row -->

                <div class="row">

                    <div class="col-lg-12">

                        <form action="" method="post">

                            <div class="panel panel-default">

                                <div class="panel-heading">

                                  Form Assign staff untuk Kegiatan :

                                </div>

                                <!-- /.panel-heading -->

                                <div class="panel-body">

                                    <div class="dataTable_wrapper">

                                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">                                   

                                            <tbody>

                                                <tr><th>Nama Kegiatan</th><td><?php echo $data['nama_kegiatan']?>

                                                </td></tr>

                                                <tr><th>Negara</th><td><?php echo $data['country']?></td></tr>

                                                <tr><th>District</th><td><?php echo $data['district']?></td></tr>

                                                <tr><th>Tempat</th><td><?php echo $data['tempat_kegiatan']?></td>

                                                </tr>

                                                <tr><th>Waktu</th><td><?php echo $tm." - ".$ts; ?></td></tr>

                                                <tr><th>Kordinator Kegiatan </th><td> 

                                                    <select class="pilihBanyak" name="status_kordinator" required>

                                                        <option value="">-Pilih Koordinator-</option>

                                                        <?php

                                                        $cek = $AssignStaffDN->cekdulu($id);

                                                        $ceklagi  = $cek->fetch_array();

                                                        $aa = $Staff->View();

                                                        while($data = $aa->fetch_array()){?>

                                                                <option value="<?php echo"$data[id]";?>"<?php

                                                                if ($data['id']==$ceklagi['nama']) {

                                                                    echo "selected";

                                                                }

                                                                 ?>><?php echo"$data[nama]";?></option>

                                                                <?php

                                                            }

                                                        ?>

                                                    </select>

                                                </td>

                                                </tr>

                                                <tr><th>Staff yang bertugas</th><td> 

                                                    <select class="pilihBanyak form-control col-lg-12"  multiple="multiple" name="nama[]" ;>

                                                        <?php 

                                                        $ViewCheckPen=$AssignStaffDN->ViewStaffbyKegiatan($id);

                                                        $datacheckpen=array();

                                                        while($data=$ViewCheckPen->fetch_array()){

                                                            $datacheckpen[]=$data['id'];

                                                        }

                                                        $ss=$AssignStaffDN->View();

                                                        while($data=$ss->fetch_array()){

                                                            echo "<option value='{$data['id']}' ".(in_array($data['id'],$datacheckpen)?" selected":"").">{$data['nama']}</option>";

                                                        }

                                                        ?>

                                                    </select>

                                                    </td>

                                                </tr>

                                                <tr>

                                                    <th></th>

                                                    <td> 

                                                        <button class="btn btn-success"  name="assignStaff">Assign Tim</button>

                                                        <a href="monitoring.php?active=<?php echo base64_encode(2);?>" class="btn btn-danger" role="button">Cancel</a>

                                                    </td>

                                                </tr>         

                                            </tbody>

                                        </table>

                                    </div>

                                    <!-- /.dataTable_wrapper -->



                                    <!-- Modal -->

                                    <div class="modal fade" id="myModal" role="dialog">

                                        <div class="modal-dialog">  

                                            <!-- Modal content-->

                                            <div class="modal-content">

                                                <div class="modal-header">

                                                    <button type="button" class="close" data-dismiss="modal"></button>

                                                    <h4 class="modal-title">Keterangan</h4>

                                                </div>

                                                <div class="modal-body">

                                                    <p>Data Berhasil Di Update.</p>

                                                    <?php

                                                        if($mode==1){ $no=1;?>

                                                            <p><?php echo " Namun terdapat staff yang tidak bisa di assign karena sudah ditugaskan pada kegiatan lain atau belum mengupload laporan perjalanan sebelumnya, yaitu:<br>";

                                                                foreach ($existingStaffName as $item) {

                                                                    echo "<br />$no. $item";

                                                                    $no++;

                                                                }

                                                            ?>.

                                                            </p>

                                                        <?php

                                                        }?>          

                                                </div>

                                                <div class="modal-footer">

                                                    <button id="buttonModal" type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <!-- /.modal -->

                                </div>

                                <!-- /.panel-body -->

                            </div>

                            <!-- /.panel -->

                        </form>

                    </div>

                    <!-- /.col-lg-12 -->

                </div>           

                <!-- /.row -->

            </div>

            <!-- /#page-wrapper -->



        </div>

        <!-- /#wrapper -->



        <!-- jQuery 

        <script src="bower_components/jquery/dist/jquery.min.js"></script> -->



        <!-- Bootstrap Core JavaScript -->

        <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>



        <!-- Metis Menu Plugin JavaScript -->

        <script src="bower_components/metisMenu/dist/metisMenu.min.js"></script>



        <!-- Custom Theme JavaScript -->

        <script src="dist/js/sb-admin-2.js"></script>



        <!-- DataTables JavaScript -->

        <script src="bower_components/datatables/media/js/jquery.dataTables.min.js"></script>

        <script src="bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>



        <script>

            $(document).ready(function() {

                <?php

                if($mode!=0){?>

                    $("#myModal").modal({backdrop: "static"});

                <?php

                }

                ?>

                $("#buttonModal").click(function(){

                    document.location.href='monitoring.php?active=<?php echo base64_encode(2);?>';

                });

                $('#dataTables-example').DataTable({

                        responsive: true

                });

            });

        </script>



    </body>

</html>

