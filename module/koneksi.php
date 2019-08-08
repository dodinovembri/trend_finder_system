<?php 
$koneksi = mysqli_connect("localhost", "root", "", "skripsii");
if (!$koneksi) {
	die("Database MySql tidak dapat dibuka");
}