<?php
  //memanggil file head pada folder module  
  include_once 'module/head.php';
  include_once 'module/koneksi.php';
  include "twitteroauth/twitteroauth.php";
  require_once ('module/stemming.php');
  include_once 'module/cluster.php';
  include_once 'module/WordCloud.php';
?>
<!-- awal body -->
<body class="hold-transition skin-blue sidebar-mini" >
  <div class="wrapper">

<!-- memanggil header  -->
<?php  
  //memanggil file header pada folder module 
  include_once 'module/header.php';
  //memanggil menu left pada folder mdule
  include_once 'module/leftbar.php';
?>

<!-- ----------------------------------------------   wordcloud   ----------------------------------------------------- -->
  <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="margin-top: 50px;">
      <section class="content">
        <!-- /.row -->

        <div class="row" id="1">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h2><b><center>Results of <span style="color: #3c8dbc;">TREND</span></center></b></h2>
                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <!-- /.box-header -->
              <div class="box-body">
                <div class="row">
                  <div class="col-md-12">
                    <?php 
                    ini_set("display_errors", "Off"); 
                    ini_set('max_execution_time', 0);                 

                    // mulai ambil twit
                    $consumer_key = "Dt3llBcmMDWOQ5mWCnDMEBASj";
                    $consumer_secret = "1K2BHCma41OHCOsIQpHyqY6JowFXHPANNaKwXC9ixEzu8r1IO1";
                    $access_token = "1108956402-4VkV12yyaufXl6JyzmKQltCClwcuidz0hEKytvD";
                    $access_token_secret = "FO898FRVTMSMsUZLzwWYsRyJacZxTJgZAXg6331QXZ3aP";

                    $twitter = new TwitterOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);
                    
                    $hapus_twit = "TRUNCATE TABLE twit";
                    $eksekusi_twit = mysqli_query($koneksi, $hapus_twit);

                    if (isset($_POST['keyword'])) {
                      $mau_dihapus = $_POST['keyword'];
                      $n = 0;
                      $tweets = $twitter->get('https://api.twitter.com/1.1/search/tweets.json?q='.$_POST['keyword'].'&result_type=recent&count=5');
                      foreach ($tweets as $tweet){
                        foreach ($tweet as $t) {
                          $n++;
                          $a = $n;
                          $b = $t->text;

                          $query_insert_twit = "INSERT INTO `twit` (`id`, `tweet`) VALUES ('$a', '$b')";
                          $hasil_insert_twit = mysqli_query($koneksi, $query_insert_twit);
                        }
                      }
                    }
                    // ahir ambil twit

                    // mulai preprocess 
                    $query_select_twit = "SELECT * FROM twit";
                    $hasil_select_twit = mysqli_query($koneksi, $query_select_twit);

                    $query_stopwords = "SELECT * FROM stopwords";
                    $hasil_stopwords = mysqli_query($koneksi, $query_stopwords);

                    $arr = array();
                      foreach ($hasil_stopwords as $k) {
                        array_push($arr, $k['kata']);
                      }

                      function removeCommonWords($input) //fungsi stopword
                        {
                          $commonWords = $GLOBALS['arr'];
                          return preg_replace('/\b('.implode('|',$commonWords).')\b/','',$input);
                        }

                      $hapus_hasil = "TRUNCATE TABLE hasil";
                      $eksekusi_hasil = mysqli_query($koneksi, $hapus_hasil);
                        foreach ($hasil_select_twit as $k) {
                          $a = $k['id']." ". $k['tweet'];
                          $lower = strtolower($a); //to lower case
                          
                          $string = $lower;
                          $remove = removeCommonWords($string); //stopwords
                          
                          $token = strtok($remove, " "); //tokenizing
                          $id = $k['id'];

                            while ($token !== false) {
                              $token = strtok(" ");
                              $stemming = stemming($token); //manggil fungsi stemming
                              
                                if ($stemming) {
                                  $query_insert_hasil = "INSERT INTO hasil (`id`, `kata_dasar`) VALUES ('$id', '$stemming')";                 
                                  $hasil_insert_hasil = mysqli_query($koneksi, $query_insert_hasil);
                                }    
                            }
                        }

                        //syntax untuk update field jumlah
                        $query_select_hasil = "SELECT id, count(id) as jumlah FROM hasil group by id";
                        $hasil_select_hasil = mysqli_query($koneksi, $query_select_hasil);
                        foreach ($hasil_select_hasil as $k) {
                          $jumlah = $k['jumlah'];
                          $id = $k['id'];

                          $query_update_hasil = "UPDATE hasil SET `jumlah`='$jumlah' where `id`='$id'"; //update field jumlah 
                          $hasil_update_hasil = mysqli_query($koneksi, $query_update_hasil); 
                          }

                        // syntax insert into table tf dengan field-fieldnya
                        $query_select_hasil2 = "SELECT id, kata_dasar, jumlah,  count(kata_dasar) as jumlah_kd from hasil group by kata_dasar, id";
                        $hasil_select_hasil2 = mysqli_query($koneksi, $query_select_hasil2);

                        $hapus_tf = "TRUNCATE TABLE tf";
                        $eksekusi_tf = mysqli_query($koneksi, $hapus_tf);

                        foreach ($hasil_select_hasil2 as $hkd) {
                          $id = $hkd['id'];
                          $kata_dasar = $hkd['kata_dasar'];
                          $jumlah_perdokumen = $hkd['jumlah'];
                          $jumlah_kd = $hkd['jumlah_kd'];

                          $query_insert_tf = "INSERT INTO `tf` (`id_dokumen`, `kata_dasar`, `jumlah_kata_dasar`,`jumlah_perdokumen`) VALUES ('$id', '$kata_dasar', '$jumlah_kd', '$jumlah_perdokumen')"; //insert field jumlah kata dasar
                          $hasil_insert_tf = mysqli_query($koneksi, $query_insert_tf); 

                          // echo $hkd['id']. " " .$hkd['kata_dasar'] ." " .$hkd['jumlah_kd'];
                          // echo "<br>";
                        }

                        //syntax untuk menghitung nilai tf
                        $query_select_tf ="SELECT * FROM tf";
                        $hasil_select_tf = mysqli_query($koneksi, $query_select_tf);
                        foreach ($hasil_select_tf as $htf) {
                          $id = $htf['id'];
                          $tf = $htf['jumlah_kata_dasar']/$htf['jumlah_perdokumen']; //menghitung nilai tf
                          
                          $query_update_tf = "UPDATE tf SET `tf`='$tf' where `id`='$id'";
                          $hasil_update_tf = mysqli_query($koneksi, $query_update_tf);

                        }

                        // mencari nilai idf
                        $query_select_tf2 ="SELECT count(id_dokumen) as jumlah FROM tf group by id_dokumen";
                        $hasil_select_tf2 = mysqli_query($koneksi, $query_select_tf2);

                        //membuat array untk menghitung jumlah
                        $array = array();
                        while($row = mysqli_fetch_assoc($hasil_select_tf2)){
                          $jml_dokumen[] = $row['jumlah'];
                        }

                        // echo (log10(18));

                        //jumlah dokumen
                        $jumlah_dokumen = count($jml_dokumen);

                        $hapus_idf = "TRUNCATE TABLE idf";
                        $eksekusi_idf = mysqli_query($koneksi, $hapus_idf);

                        //mencari jmlah dokumen yang mengandung kata yang sama
                        $query_select_tf3 = "SELECT id_dokumen, kata_dasar, jumlah_kata_dasar, jumlah_perdokumen, tf,  count(kata_dasar) as jumlah_kd from tf group by kata_dasar";
                        $hasil_select_tf3 = mysqli_query($koneksi, $query_select_tf3);
                        foreach ($hasil_select_tf3 as $hkd) {
                          $id_dokumen = $hkd['id_dokumen'];
                          $kata_dasar = $hkd['kata_dasar'];
                          $jumlah_kata_dasar = $hkd['jumlah_kata_dasar'];
                          $jumlah_perdokumen = $hkd['jumlah_perdokumen'];
                          $jumlah_tf = $hkd['tf'];
                          $jumlah_kd = $hkd['jumlah_kd'];
                          $idf = (log10($jumlah_dokumen/$jumlah_kd)); //menghitung nilai idf
                          $tfidfb = $jumlah_tf*$idf;  
                          $tfidf = floatval($tfidfb);


                          $query_insert_idf = "INSERT INTO `idf` (`id_dokumen`, `kata_dasar`, `jumlah_kata_dasar`,`jumlah_perdokumen`, `jumlah_dokumen`, `jml_dalam_dokumen`, `tf`, `idf`, `tfidf`) VALUES ('$id_dokumen', '$kata_dasar', '$jumlah_kata_dasar', '$jumlah_perdokumen', '$jumlah_dokumen', '$jumlah_kd', '$jumlah_tf', '$idf', '$tfidf')"; //insert field jumlah kata dasar
                          $hasil_insert_idf = mysqli_query($koneksi, $query_insert_idf); 
                        }

                        $query_select_tfidf = "SELECT tfidf FROM idf";
                        $hasil_select_tfidf = mysqli_query($koneksi, $query_select_tfidf);

                        $array = array();
                        while($row = mysqli_fetch_assoc($hasil_select_tfidf)){
                          $kata[] = floatval($row['tfidf']);
                        }

                        $hasil = kmeans($kata, 3); //melakukan clustering dengan memanggil fungsi kmeans

                        $a =0;
                        echo "<br><br>";
                        foreach ($hasil as $k) {
                          $a++;
                          foreach ($k as $w) {
                            // echo $w. " ";
                            $query3 = "UPDATE idf set `cluster`='$a' where `tfidf`='$w'";
                              $hasil3 = mysqli_query($koneksi, $query3);
                          }
                          // echo "<br>";
                          // echo "<br>";
                        }

                      ?>             
                    <?php  
                        $query_adata = "SELECT kata_dasar, cluster FROM idf ORDER BY cluster ASC";
                        $hasil_adata = mysqli_query($koneksi, $query_adata);
                    ?>

                     <?php 
                    foreach ($hasil_adata as $grafik) {
                      $persen[] = $grafik['kata_dasar'];
                      $jumlah_adata[] = $grafik['cluster'];
                    }

                    foreach ($persen as $key => $var) {
                        $array[] = $var;
                    }

                    foreach ($jumlah_adata as $key2 => $var2) {
                        $array2[] = (int)$var2;
                    }

                    ?>

                <div class="box-body" style="width: 50%; text-align: center; vertical-align: middle; margin-left: 25%;">
                <div class="row" id="1">
                  <div class="col-md-12">
                   <?php  
                    $query_select_wordcloud = "SELECT kata_dasar FROM hasil";
                    $hasil_select_wordcloud = mysqli_query($koneksi, $query_select_wordcloud);

                    foreach ($hasil_select_wordcloud as $hsw) {
                      foreach ($hsw as $khsq => $valuehsw) {
                        $kata_hsw[] = $valuehsw;
                      }
                    }

                    // print_r($kata_hsw);
                    $ubah1 = implode(" ", $kata_hsw); //menggabungkan array dengan implode
                    
                    $ubah = str_replace($mau_dihapus, '', $ubah1);
                    // echo $ubah;

                    // $txt = $a; 
                    $cloud = new WordCloud($ubah); //membuat objek dengan memanggil fungsi wordcloud
                    // echo $cloud->showCloud('ture'); // menampilkan hasil wordcloud dengan jumlahnya
                    echo $cloud->showCloud(); //tanpa jumlah
                   ?>
                    <!-- /.chart-responsive -->
                  </div>
                  <!-- /.col -->

                  <!-- /.col -->
                </div>
                <!-- /.row -->
              </div>
              <?php 
                    $query_select_ontologi = "SELECT kata_dasar, COUNT(kata_dasar) AS jumlah from hasil group by kata_dasar ORDER BY jumlah DESC limit 5";
                        $hasil_select_ontologi = mysqli_query($koneksi, $query_select_ontologi);
                        $n = 0;
                        $mau_dihapus_ontologi = $_POST['keyword'];

                        $keyword_yang_akan_disimpan = $_POST['keyword'];
                       
                        ?>

                  <h2><b><center>Top<span style="color: #3c8dbc;">5</span></center></b></h2>
                        <?php
                  foreach ($hasil_select_ontologi as $hasil_ontologi) {
                    $hapus_keyword = str_replace($mau_dihapus_ontologi, '', $hasil_ontologi['kata_dasar']);
                    
                    $n++; 
                    $tren = $hasil_ontologi['kata_dasar'];
                    $frekuensi = $hasil_ontologi['jumlah'];

                    $query_simpan_5_tren ="INSERT INTO tabel_tren (`keyword`, `tren`, `frekuensi`) VALUES ('$keyword_yang_akan_disimpan', '$tren', '$frekuensi')";
                    $hasil_simpan_5_tren = mysqli_query($koneksi, $query_simpan_5_tren);

                    ?>
                    <p style="border: solid 1px #aaa; background: #ccc; padding: 15px; -moz-border-radius: 15px; -khtml-border-radius: 15px; -webkit-border-radius: 15px; border-radius: 15px; margin: 0; text-align: center; line-height: 23px; color: black; font-size: 18px"><?php echo $_POST['keyword']." ".$hapus_keyword; ?></p>

                    <?php
                  }
              ?>
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
              </div>
            </div>
            <!-- /.box -->
          </div>
          <!-- /.col -->
        </div>


  <!-- --------------------------------------------------  nampilin clustering  ---------------------------------------- -->
     
        <div class="row" id="2">
          <div class="col-md-12">
            <div class="box" >
              <div class="box-header with-border">
                <h2><b><center>Results of <span style="color: #3c8dbc;">CLUSTERING</span></center></b></h2>

                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <!-- /.box-header -->

              <div id="container"></div>
                  <style type="text/css">
                  #container {
                    min-width: 100%;
                    max-width: 800px;
                    height: 400px;
                    margin: 0 auto
                  }
                  </style>

                  <script type="text/javascript">
                                    
                  Highcharts.chart('container', {

                    title: {
                      text: '  '
                    },

                    yAxis: {
                      title: {
                        text: 'Cluster'
                      }
                    },
                    legend: {
                      layout: 'vertical',
                      align: 'right',
                      verticalAlign: 'middle'
                    },
                     xAxis: {
                                      categories: <?php echo json_encode($array); ?>,
                                  },

                    series: [{
                      name: 'Installation',
                      data: <?php echo json_encode($array2); ?>
                    }],

                    responsive: {
                      rules: [{
                        condition: {
                          maxWidth: 500
                        },
                        chartOptions: {
                          legend: {
                            layout: 'Line Kata',
                            align: 'center',
                            verticalAlign: 'bottom'
                          }
                        }
                      }]
                    }

                  });
                </script>
              

              <!-- /.box-footer -->
            </div>
            <!-- /.box -->
          </div>
          <!-- /.col -->
        </div>
  <!-- ---------------------------------------------   Nampilin Ontologi   ---------------------------------------------- -->

        <div class="row" id="3">
          <div class="col-md-12" style="height: 450px;">
            <div class="box">
              <div class="box-header with-border">
                <h2><b><center>Results of <span style="color: #3c8dbc;">SYNONYM </span></center></b></h2>

                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <!-- /.box-header -->
              <div class="box-body">
                <div class="row">
                  <div class="col-md-12" style="height: 400px;">
                      
                      <iframe src="module/sinonim.php" width="100%" height="100%"></iframe>
                    
                    <!-- /.chart-responsive -->
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
              </div>
              <!-- /.box-footer -->
            </div>
            <!-- /.box -->
          </div>
          <!-- /.col -->
        </div>

  <!-- ------------------------------------------------------------------------------------------------------------------ -->
  <!-- --------------------------------------------------  nampilin clustering  ---------------------------------------- -->
     
        <div class="row" id="3">
          <div class="col-md-12" style="height: 450px;">
            <div class="box">
              <div class="box-header with-border">
                <h2><b><center>Results of <span style="color: #3c8dbc;">TREN Before </span></center></b></h2>

                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <!-- /.box-header -->
              <div class="box-body">
                <div class="row">
                  <div class="col-md-12" style="height: 400px;">
                      <?php 
                      $keyword_inputan = $_POST['keyword'];
                      $query_tren_sebelumnya = "SELECT tren, SUM(frekuensi) AS jumlah FROM tabel_tren WHERE keyword='$keyword_inputan' GROUP BY  tren ORDER BY frekuensi DESC LIMIT 5";
                      $hasil_tren_sebelumnya = mysqli_query($koneksi, $query_tren_sebelumnya);

                      

                        ?>

                        <fieldset class="fieldsett">
                          <legend>Top 5 Tren Hasil</legend><?php
                          foreach ($hasil_tren_sebelumnya as $hasil_tsb) {?>
                            <form action="" method="post">
                              <input class="formm" style="width: 100%" type="submit" name="kk" value="<?php echo $hasil_tsb['tren'];?>" >
                            </form>
                        </fieldset>

                        <?php


                      }
                       ?>
                    
                    <!-- /.chart-responsive -->
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
              </div>
              <!-- /.box-footer -->
            </div>
            <!-- /.box -->
          </div>
          <!-- /.col -->
        </div>



<!-- --------------------------------------------------  Nampilin Yang Udah Disimpan  ---------------------------------------- -->
     
              


              <!-- /.box-footer -->
            </div>
            <!-- /.box -->
          </div>
          <!-- /.col -->
        </div>
      </section>
    </div>
  <!-- /.content-wrapper -->
<?php  
  include_once 'module/footer.php';
?>

