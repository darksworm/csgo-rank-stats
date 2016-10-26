<?php

if(isset($_GET['rank'])){
    include('one.php');
} else {
    include('all.php');
}